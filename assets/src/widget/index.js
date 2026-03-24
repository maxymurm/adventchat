/**
 * AdventChat Widget — Full chat engine.
 *
 * Implements: WP-30 (shell), WP-31 (injection), WP-32 (session),
 * WP-33 (send), WP-34 (receive), WP-35 (typing), WP-36 (read receipts),
 * WP-37 (visitor info), WP-38 (sound), WP-54 (pre-chat form),
 * WP-55 (offline form), WP-57 (transcript email), WP-58 (CSAT),
 * WP-60 (file sharing), WP-61 (GDPR consent).
 *
 * @package AdventChat
 */

(function () {
  'use strict';

  if (typeof window.adventchatConfig === 'undefined') return;

  var config = window.adventchatConfig;
  var settings = config.settings || {};
  var siteId = config.siteId;
  var identity = config.identity || null;
  var woo = config.woo || null;

  /* ------------------------------------------------------------------ */
  /*  Firebase init                                                      */
  /* ------------------------------------------------------------------ */
  var app = firebase.initializeApp(config.firebase);
  var auth = firebase.auth();
  var db = firebase.firestore();

  /* ------------------------------------------------------------------ */
  /*  State                                                              */
  /* ------------------------------------------------------------------ */
  var state = {
    open: false,
    sessionId: null,
    user: null,
    unsubMessages: null,
    unsubTyping: null,
    visitorName: '',
    visitorEmail: '',
    unreadCount: 0,
  };

  /* ------------------------------------------------------------------ */
  /*  SVG icons                                                          */
  /* ------------------------------------------------------------------ */
  var ICON_CHAT = '<svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/><path d="M7 9h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"/></svg>';
  var ICON_CLOSE = '<svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
  var ICON_SEND = '<svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>';
  var ICON_ATTACH = '<svg viewBox="0 0 24 24"><path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/></svg>';

  /* ------------------------------------------------------------------ */
  /*  WP-37: Visitor info collector                                      */
  /* ------------------------------------------------------------------ */
  function getVisitorInfo() {
    var ua = navigator.userAgent;
    var lang = navigator.language || '';
    return {
      userAgent: ua,
      language: lang,
      pageUrl: window.location.href,
      pageTitle: document.title,
      referrer: document.referrer || '',
      screenWidth: screen.width,
      screenHeight: screen.height,
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || '',
    };
  }

  /* ------------------------------------------------------------------ */
  /*  WP-38: Sound notifications                                         */
  /* ------------------------------------------------------------------ */
  function playNotificationSound() {
    if (settings.soundEnabled !== '1') return;
    try {
      var AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      var ctx = new AudioCtx();
      var osc = ctx.createOscillator();
      var gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.frequency.value = 800;
      gain.gain.value = 0.3;
      osc.start();
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
      osc.stop(ctx.currentTime + 0.3);
    } catch (e) { /* ignore audio errors */ }
  }

  /* ------------------------------------------------------------------ */
  /*  WP-55: Offline form builder                                        */
  /* ------------------------------------------------------------------ */
  function buildOfflineForm() {
    if (settings.offlineEnabled !== '1') return '';
    var html =
      '<div class="ac-offline" id="ac-offline" style="display:none;">' +
        '<p class="ac-offline__text">We are currently offline. Leave a message and we\'ll get back to you.</p>' +
        '<div class="ac-prechat__field"><label class="ac-prechat__label" for="ac-off-name">Name</label><input class="ac-prechat__input" id="ac-off-name" type="text" required /></div>' +
        '<div class="ac-prechat__field"><label class="ac-prechat__label" for="ac-off-email">Email</label><input class="ac-prechat__input" id="ac-off-email" type="email" required /></div>' +
        '<div class="ac-prechat__field"><label class="ac-prechat__label" for="ac-off-msg">Message</label><textarea class="ac-prechat__input" id="ac-off-msg" rows="3" required></textarea></div>';

    if (settings.gdprEnabled === '1') {
      html += '<label class="ac-prechat__consent"><input type="checkbox" id="ac-off-consent" /><span>I agree to the processing of my personal data.</span></label>';
    }

    html += '<button class="ac-prechat__btn" id="ac-off-submit" type="button">Send Message</button>' +
      '<p class="ac-offline__success" id="ac-off-success" style="display:none;color:#22c55e;text-align:center;font-size:13px;">Message sent! We\'ll be in touch soon.</p>' +
      '</div>';
    return html;
  }

  function submitOfflineMessage() {
    var name = document.getElementById('ac-off-name');
    var email = document.getElementById('ac-off-email');
    var msg = document.getElementById('ac-off-msg');
    var consent = document.getElementById('ac-off-consent');

    if (!name || !name.value.trim()) { if (name) name.focus(); return; }
    if (!email || !email.value.trim()) { email.focus(); return; }
    if (!msg || !msg.value.trim()) { msg.focus(); return; }
    if (consent && !consent.checked) return;

    var submitBtn = document.getElementById('ac-off-submit');
    if (submitBtn) submitBtn.disabled = true;

    fetch(config.restUrl + '/offline-message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.restNonce },
      body: JSON.stringify({ name: name.value.trim(), email: email.value.trim(), message: msg.value.trim(), consent: consent ? consent.checked : false }),
    }).then(function () {
      var success = document.getElementById('ac-off-success');
      if (success) success.style.display = 'block';
      if (submitBtn) submitBtn.style.display = 'none';
      name.value = ''; email.value = ''; msg.value = '';
    }).catch(function () {
      if (submitBtn) submitBtn.disabled = false;
    });
  }

  /* ------------------------------------------------------------------ */
  /*  WP-58: CSAT rating                                                 */
  /* ------------------------------------------------------------------ */
  function buildCsatHtml() {
    return '<div class="ac-csat" id="ac-csat" style="display:none;">' +
      '<p class="ac-csat__title">How was your chat?</p>' +
      '<div class="ac-csat__stars">' +
        '<button class="ac-csat__star" data-rating="1" type="button">★</button>' +
        '<button class="ac-csat__star" data-rating="2" type="button">★</button>' +
        '<button class="ac-csat__star" data-rating="3" type="button">★</button>' +
        '<button class="ac-csat__star" data-rating="4" type="button">★</button>' +
        '<button class="ac-csat__star" data-rating="5" type="button">★</button>' +
      '</div>' +
      '<textarea class="ac-prechat__input" id="ac-csat-comment" rows="2" placeholder="Any feedback? (optional)"></textarea>' +
      '<button class="ac-prechat__btn" id="ac-csat-submit" type="button">Submit</button>' +
      '</div>';
  }

  function showCsat() {
    if (settings.csatEnabled !== '1') return;
    var csat = document.getElementById('ac-csat');
    var input = document.getElementById('ac-input');
    if (csat) csat.style.display = 'flex';
    if (input) input.style.display = 'none';

    var stars = document.querySelectorAll('.ac-csat__star');
    var selectedRating = 0;
    stars.forEach(function (s) {
      s.addEventListener('click', function () {
        selectedRating = parseInt(s.getAttribute('data-rating'));
        stars.forEach(function (ss) {
          ss.classList.toggle('ac-csat__star--active', parseInt(ss.getAttribute('data-rating')) <= selectedRating);
        });
      });
    });

    var submitBtn = document.getElementById('ac-csat-submit');
    if (submitBtn) {
      submitBtn.addEventListener('click', function () {
        if (!selectedRating || !state.sessionId) return;
        var comment = document.getElementById('ac-csat-comment');
        db.collection('sessions').doc(state.sessionId).update({
          rating: selectedRating,
          ratingComment: comment ? comment.value.trim() : '',
        }).then(function () {
          csat.innerHTML = '<p class="ac-csat__title">Thank you for your feedback!</p>';
        });
      });
    }
  }

  /* ------------------------------------------------------------------ */
  /*  WP-57: Transcript email                                            */
  /* ------------------------------------------------------------------ */
  function sendTranscript() {
    if (!state.sessionId || !state.visitorEmail) return;
    var msgs = [];
    var container = document.getElementById('ac-messages');
    if (container) {
      container.querySelectorAll('.ac-message').forEach(function (el) {
        msgs.push({
          senderName: el.classList.contains('ac-message--visitor') ? state.visitorName : 'Agent',
          senderType: el.classList.contains('ac-message--visitor') ? 'visitor' : (el.classList.contains('ac-message--system') ? 'system' : 'agent'),
          text: el.textContent || '',
          timestamp: '',
        });
      });
    }

    fetch(config.restUrl + '/transcript', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.restNonce },
      body: JSON.stringify({ email: state.visitorEmail, name: state.visitorName, messages: msgs }),
    });
  }

  /* ------------------------------------------------------------------ */
  /*  WP-60: File/image sharing (Firebase Storage)                       */
  /* ------------------------------------------------------------------ */
  var FILE_MAX = 10 * 1024 * 1024; // 10MB

  function handleFileUpload(file) {
    if (!file || !state.sessionId || !state.user) return;
    if (file.size > FILE_MAX) {
      addSystemMessage('File too large. Maximum size is 10MB.');
      return;
    }

    // Use Firebase Storage if available.
    if (typeof firebase.storage !== 'function') {
      addSystemMessage('File sharing is not available.');
      return;
    }

    var storage = firebase.storage();
    var path = 'chats/' + state.sessionId + '/' + Date.now() + '_' + file.name;
    var ref = storage.ref(path);

    addSystemMessage('Uploading ' + file.name + '...');

    ref.put(file).then(function (snap) {
      return snap.ref.getDownloadURL();
    }).then(function (url) {
      var isImage = /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(file.name);
      var text = isImage
        ? '![' + escHtml(file.name) + '](' + url + ')'
        : '[📎 ' + escHtml(file.name) + '](' + url + ')';

      var messageData = {
        senderUid: state.user.uid,
        senderName: state.visitorName || 'Visitor',
        senderType: 'visitor',
        text: text,
        fileUrl: url,
        fileName: file.name,
        fileType: isImage ? 'image' : 'file',
        timestamp: firebase.firestore.FieldValue.serverTimestamp(),
        readByAgent: false,
        readByVisitor: true,
      };

      db.collection('sessions').doc(state.sessionId)
        .collection('messages').add(messageData);
    }).catch(function (err) {
      addSystemMessage('Upload failed: ' + err.message);
    });
  }

  /* ------------------------------------------------------------------ */
  /*  WP-30/31: Build and inject widget DOM                              */
  /* ------------------------------------------------------------------ */
  function buildWidget() {
    var pos = settings.position || 'bottom-right';
    var root = document.createElement('div');
    root.className = 'adventchat-widget adventchat-widget--' + pos;
    root.style.setProperty('--ac-primary', settings.primaryColor || '#0066ff');
    root.style.setProperty('--ac-secondary', settings.secondaryColor || '#ffffff');

    // WP-64: Custom X/Y offsets.
    var offsetX = (settings.offsetX || 20) + 'px';
    var offsetY = (settings.offsetY || 20) + 'px';
    root.style.bottom = offsetY;
    if (pos === 'bottom-right') { root.style.right = offsetX; } else { root.style.left = offsetX; }

    // WP-64: Launcher style.
    var launcherStyle = settings.launcherStyle || 'bubble';
    var launcherClass = 'ac-launcher';
    if (launcherStyle === 'tab') launcherClass += ' ac-launcher--tab';
    if (launcherStyle === 'custom-image') launcherClass += ' ac-launcher--custom';

    var launcherContent = '';
    if (launcherStyle === 'custom-image' && settings.launcherImage) {
      launcherContent =
        '<img class="ac-launcher__img" src="' + escAttr(settings.launcherImage) + '" alt="Chat" />';
    } else {
      launcherContent =
        '<span class="ac-icon-chat">' + ICON_CHAT + '</span>' +
        '<span class="ac-icon-close">' + ICON_CLOSE + '</span>';
    }

    root.innerHTML =
      '<div class="ac-window" id="ac-window">' +
        '<div class="ac-header">' +
          '<p class="ac-header__title">' + escHtml(settings.welcomeTitle || 'Hi there!') + '</p>' +
          '<p class="ac-header__subtitle">' + escHtml(settings.welcomeSubtitle || 'How can we help you?') + '</p>' +
          '<p class="ac-header__status" id="ac-status">Online</p>' +
        '</div>' +
        buildPrechatForm() +
        '<div class="ac-messages" id="ac-messages" style="display:none;"></div>' +
        '<div class="ac-typing" id="ac-typing"><span></span><span class="ac-typing__dots"><span></span><span></span><span></span></span></div>' +
        '<div class="ac-input" id="ac-input" style="display:none;">' +
          (settings.fileSharing === '1' ? '<button class="ac-input__attach" id="ac-attach" type="button" title="Attach file">' + ICON_ATTACH + '</button><input type="file" id="ac-file-input" style="display:none;" />' : '') +
          '<textarea class="ac-input__text" id="ac-text" rows="1" placeholder="' + escAttr(settings.placeholder || 'Type a message\u2026') + '"></textarea>' +
          '<button class="ac-input__send" id="ac-send" type="button">' + ICON_SEND + '</button>' +
        '</div>' +
        buildOfflineForm() +
        buildCsatHtml() +
        '<div class="ac-footer-actions" id="ac-footer-actions" style="display:none;">' +
          '<button class="ac-footer-btn" id="ac-transcript-btn" type="button">Email transcript</button>' +
        '</div>' +
        '<div class="ac-powered">Powered by <a href="https://adventchat.com" target="_blank" rel="noopener">AdventChat</a></div>' +
      '</div>' +
      '<button class="' + launcherClass + '" id="ac-launcher" type="button" aria-label="Toggle chat">' +
        launcherContent +
        '<span class="ac-badge" id="ac-badge">0</span>' +
      '</button>';

    if (launcherStyle === 'tab') {
      var launcherEl = root.querySelector('#ac-launcher');
      if (launcherEl) launcherEl.innerHTML += '<span class="ac-launcher__label">Chat</span>';
    }

    document.body.appendChild(root);
    bindEvents(root);

    // WP-66: Auto-open with delay.
    if (settings.autoOpenEnabled === '1' && !sessionStorage.getItem('ac_closed')) {
      var delayMs = (parseInt(settings.autoOpenDelay, 10) || 5) * 1000;
      setTimeout(function () {
        if (!state.open) {
          var chatWindow = root.querySelector('#ac-window');
          var launcher = root.querySelector('#ac-launcher');
          state.open = true;
          if (chatWindow) chatWindow.classList.add('ac-window--open');
          if (launcher) launcher.classList.add('ac-launcher--open');
        }
      }, delayMs);
    }
  }

  function buildPrechatForm() {
    if (settings.prechatEnabled !== '1') return '';

    // WP-72/77: Pre-fill values from WooCommerce or identity verification.
    var prefillName = settings.prefillName || (identity ? identity.name : '') || '';
    var prefillEmail = settings.prefillEmail || (identity ? identity.email : '') || '';

    var html =
      '<div class="ac-prechat" id="ac-prechat">' +
        '<div class="ac-prechat__field">' +
          '<label class="ac-prechat__label" for="ac-name">Name</label>' +
          '<input class="ac-prechat__input" id="ac-name" type="text" value="' + escAttr(prefillName) + '" required />' +
        '</div>' +
        '<div class="ac-prechat__field">' +
          '<label class="ac-prechat__label" for="ac-email">Email</label>' +
          '<input class="ac-prechat__input" id="ac-email" type="email" value="' + escAttr(prefillEmail) + '" required />' +
        '</div>';

    if (settings.gdprEnabled === '1') {
      html +=
        '<label class="ac-prechat__consent">' +
          '<input type="checkbox" id="ac-consent" />' +
          '<span>I agree to the processing of my personal data.</span>' +
        '</label>';
    }

    html += '<button class="ac-prechat__btn" id="ac-start-chat" type="button">Start Chat</button></div>';
    return html;
  }

  /* ------------------------------------------------------------------ */
  /*  Event binding                                                      */
  /* ------------------------------------------------------------------ */
  function bindEvents(root) {
    var launcher = root.querySelector('#ac-launcher');
    var chatWindow = root.querySelector('#ac-window');
    var sendBtn = root.querySelector('#ac-send');
    var textInput = root.querySelector('#ac-text');
    var startBtn = root.querySelector('#ac-start-chat');

    launcher.addEventListener('click', function () {
      state.open = !state.open;
      chatWindow.classList.toggle('ac-window--open', state.open);
      launcher.classList.toggle('ac-launcher--open', state.open);
      if (state.open) {
        state.unreadCount = 0;
        updateBadge();
        if (!startBtn && !state.sessionId) startChat();
      } else {
        // WP-66: Mark as manually closed so auto-open doesn't fire again.
        sessionStorage.setItem('ac_closed', '1');
      }
    });

    if (sendBtn) {
      sendBtn.addEventListener('click', function () { sendMessage(); });
    }

    if (textInput) {
      textInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          sendMessage();
        }
      });
      // WP-35: typing indicator
      var typingTimeout;
      textInput.addEventListener('input', function () {
        setVisitorTyping(true);
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(function () { setVisitorTyping(false); }, 2000);
      });
    }

    if (startBtn) {
      startBtn.addEventListener('click', function () {
        var nameInput = root.querySelector('#ac-name');
        var emailInput = root.querySelector('#ac-email');
        var consentInput = root.querySelector('#ac-consent');

        if (nameInput && !nameInput.value.trim()) { nameInput.focus(); return; }
        if (emailInput && !emailInput.value.trim()) { emailInput.focus(); return; }
        if (consentInput && !consentInput.checked) { return; }

        state.visitorName = nameInput ? nameInput.value.trim() : 'Visitor';
        state.visitorEmail = emailInput ? emailInput.value.trim() : '';

        root.querySelector('#ac-prechat').style.display = 'none';
        startChat();
      });
    }

    // WP-55: Offline form submit.
    var offSubmit = root.querySelector('#ac-off-submit');
    if (offSubmit) {
      offSubmit.addEventListener('click', submitOfflineMessage);
    }

    // WP-60: File attach.
    var attachBtn = root.querySelector('#ac-attach');
    var fileInput = root.querySelector('#ac-file-input');
    if (attachBtn && fileInput) {
      attachBtn.addEventListener('click', function () { fileInput.click(); });
      fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files[0]) {
          handleFileUpload(fileInput.files[0]);
          fileInput.value = '';
        }
      });
    }

    // WP-57: Transcript email button.
    var transcriptBtn = root.querySelector('#ac-transcript-btn');
    if (transcriptBtn) {
      transcriptBtn.addEventListener('click', function () {
        sendTranscript();
        transcriptBtn.textContent = 'Transcript sent!';
        transcriptBtn.disabled = true;
      });
    }
  }

  /* ------------------------------------------------------------------ */
  /*  WP-32: Session creation in Firestore                               */
  /* ------------------------------------------------------------------ */
  function startChat() {
    if (!state.user) return;

    var messagesEl = document.getElementById('ac-messages');
    var inputEl = document.getElementById('ac-input');
    if (messagesEl) messagesEl.style.display = 'flex';
    if (inputEl) inputEl.style.display = 'flex';

    var visitorInfo = getVisitorInfo();

    var sessionData = {
      siteId: siteId,
      visitorUid: state.user.uid,
      visitorName: state.visitorName || 'Visitor',
      visitorEmail: state.visitorEmail || '',
      status: 'waiting',
      department: '',
      agentUid: '',
      agentName: '',
      startedAt: firebase.firestore.FieldValue.serverTimestamp(),
      lastMessageAt: firebase.firestore.FieldValue.serverTimestamp(),
      visitorInfo: visitorInfo,
      messageCount: 0,
      rating: 0,
      ratingComment: '',
    };

    // WP-77: Attach identity verification hash.
    if (identity && identity.hash) {
      sessionData.identityHash = identity.hash;
      sessionData.identityVerified = true;
      sessionData.wpUserId = identity.userId;
    }

    // WP-70/71/72: Attach WooCommerce context.
    if (woo) {
      sessionData.wooCommerce = woo;
    }

    db.collection('sessions').add(sessionData).then(function (docRef) {
      state.sessionId = docRef.id;
      sessionStorage.setItem('adventchat_session', docRef.id);

      addSystemMessage('Chat started. An agent will be with you shortly.');

      listenForMessages();
      listenForAgentTyping();
      listenForSessionStatus();
    }).catch(function (err) {
      console.error('[AdventChat] Session creation failed:', err.message);
      addSystemMessage('Unable to start chat. Please try again.');
    });
  }

  /* ------------------------------------------------------------------ */
  /*  WP-33: Send message                                                */
  /* ------------------------------------------------------------------ */
  function sendMessage() {
    var textInput = document.getElementById('ac-text');
    if (!textInput) return;
    var text = textInput.value.trim();
    if (!text || !state.sessionId || !state.user) return;

    textInput.value = '';
    textInput.style.height = 'auto';
    setVisitorTyping(false);

    var messageData = {
      senderUid: state.user.uid,
      senderName: state.visitorName || 'Visitor',
      senderType: 'visitor',
      text: text,
      timestamp: firebase.firestore.FieldValue.serverTimestamp(),
      readByAgent: false,
      readByVisitor: true,
    };

    db.collection('sessions').doc(state.sessionId)
      .collection('messages').add(messageData)
      .then(function () {
        db.collection('sessions').doc(state.sessionId).update({
          lastMessageAt: firebase.firestore.FieldValue.serverTimestamp(),
          messageCount: firebase.firestore.FieldValue.increment(1),
        });
      })
      .catch(function (err) {
        console.error('[AdventChat] Send failed:', err.message);
      });
  }

  /* ------------------------------------------------------------------ */
  /*  WP-34: Real-time message receiving                                 */
  /* ------------------------------------------------------------------ */
  function listenForMessages() {
    if (state.unsubMessages) state.unsubMessages();

    state.unsubMessages = db.collection('sessions').doc(state.sessionId)
      .collection('messages')
      .orderBy('timestamp', 'asc')
      .onSnapshot(function (snapshot) {
        snapshot.docChanges().forEach(function (change) {
          if (change.type === 'added') {
            var msg = change.doc.data();
            renderMessage(msg, change.doc.id);

            if (msg.senderType === 'agent' && state.open) {
              markAsRead(change.doc.id);
            }

            if (msg.senderType === 'agent') {
              if (!state.open) {
                state.unreadCount++;
                updateBadge();
              }
              playNotificationSound();
            }
          }
        });
      });
  }

  /* ------------------------------------------------------------------ */
  /*  WP-35: Typing indicators                                           */
  /* ------------------------------------------------------------------ */
  function setVisitorTyping(isTyping) {
    if (!state.sessionId || !state.user) return;
    db.collection('sessions').doc(state.sessionId)
      .collection('typing').doc(state.user.uid)
      .set({ isTyping: isTyping, timestamp: firebase.firestore.FieldValue.serverTimestamp() })
      .catch(function () {});
  }

  function listenForAgentTyping() {
    if (state.unsubTyping) state.unsubTyping();

    state.unsubTyping = db.collection('sessions').doc(state.sessionId)
      .collection('typing')
      .onSnapshot(function (snapshot) {
        var agentTyping = false;
        var agentName = '';
        snapshot.forEach(function (doc) {
          var data = doc.data();
          if (doc.id !== state.user.uid && data.isTyping) {
            agentTyping = true;
            agentName = data.name || 'Agent';
          }
        });
        var typingEl = document.getElementById('ac-typing');
        if (typingEl) {
          typingEl.querySelector('span').textContent = agentTyping ? agentName + ' is typing' : '';
          typingEl.classList.toggle('ac-typing--visible', agentTyping);
        }
      });
  }

  /* ------------------------------------------------------------------ */
  /*  WP-36: Read receipts                                               */
  /* ------------------------------------------------------------------ */
  function markAsRead(messageId) {
    db.collection('sessions').doc(state.sessionId)
      .collection('messages').doc(messageId)
      .update({ readByVisitor: true })
      .catch(function () {});
  }

  /* ------------------------------------------------------------------ */
  /*  WP-58: Listen for session status changes (for CSAT + transcript)   */
  /* ------------------------------------------------------------------ */
  function listenForSessionStatus() {
    if (!state.sessionId) return;
    db.collection('sessions').doc(state.sessionId)
      .onSnapshot(function (doc) {
        var data = doc.data();
        if (data && data.status === 'ended') {
          addSystemMessage('Chat ended.');
          var footer = document.getElementById('ac-footer-actions');
          if (footer && state.visitorEmail) footer.style.display = 'flex';
          showCsat();
          sessionStorage.removeItem('adventchat_session');
        }
      });
  }

  /* ------------------------------------------------------------------ */
  /*  DOM helpers                                                        */
  /* ------------------------------------------------------------------ */
  function renderMessage(msg, id) {
    var container = document.getElementById('ac-messages');
    if (!container) return;
    if (container.querySelector('[data-msg-id="' + id + '"]')) return;

    var el = document.createElement('div');
    el.setAttribute('data-msg-id', id);

    if (msg.senderType === 'system') {
      el.className = 'ac-message ac-message--system';
      el.textContent = msg.text;
    } else {
      var isVisitor = msg.senderType === 'visitor';
      el.className = 'ac-message ' + (isVisitor ? 'ac-message--visitor' : 'ac-message--agent');

      // WP-60: Render file/image attachments.
      if (msg.fileUrl) {
        if (msg.fileType === 'image') {
          el.innerHTML = '<img class="ac-message__img" src="' + escAttr(msg.fileUrl) + '" alt="' + escAttr(msg.fileName || 'Image') + '" />';
        } else {
          el.innerHTML = '<a class="ac-message__file" href="' + escAttr(msg.fileUrl) + '" target="_blank" rel="noopener">📎 ' + escHtml(msg.fileName || 'File') + '</a>';
        }
      } else {
        el.innerHTML = '<span>' + escHtml(msg.text) + '</span>';
      }

      if (msg.timestamp) {
        var time = document.createElement('span');
        time.className = 'ac-message__time';
        var d = msg.timestamp.toDate ? msg.timestamp.toDate() : new Date(msg.timestamp);
        time.textContent = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        el.appendChild(time);
      }
    }

    container.appendChild(el);
    container.scrollTop = container.scrollHeight;
  }

  function addSystemMessage(text) {
    var container = document.getElementById('ac-messages');
    if (!container) return;
    var el = document.createElement('div');
    el.className = 'ac-message ac-message--system';
    el.textContent = text;
    container.appendChild(el);
    container.scrollTop = container.scrollHeight;
  }

  function updateBadge() {
    var badge = document.getElementById('ac-badge');
    if (!badge) return;
    badge.textContent = state.unreadCount;
    badge.classList.toggle('ac-badge--visible', state.unreadCount > 0);
  }

  function escHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  function escAttr(str) {
    return str.replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  /* ------------------------------------------------------------------ */
  /*  Auth + boot                                                        */
  /* ------------------------------------------------------------------ */
  auth.onAuthStateChanged(function (user) {
    if (user) {
      sessionStorage.setItem('adventchat_uid', user.uid);
      state.user = user;
      window.adventchatUser = user;

      // Resume existing session if present.
      var existingSession = sessionStorage.getItem('adventchat_session');
      if (existingSession) {
        state.sessionId = existingSession;
        var messagesEl = document.getElementById('ac-messages');
        var inputEl = document.getElementById('ac-input');
        var prechatEl = document.getElementById('ac-prechat');
        if (messagesEl) messagesEl.style.display = 'flex';
        if (inputEl) inputEl.style.display = 'flex';
        if (prechatEl) prechatEl.style.display = 'none';
        listenForMessages();
        listenForAgentTyping();
        listenForSessionStatus();
      }
    } else {
      auth.signInAnonymously().catch(function (err) {
        console.error('[AdventChat] Anonymous auth failed:', err.message);
      });
    }
  });

  // Build widget DOM when ready.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', buildWidget);
  } else {
    buildWidget();
  }

  // Expose.
  window.adventchatApp = app;
  window.adventchatAuth = auth;
  window.adventchatDb = db;
})();
