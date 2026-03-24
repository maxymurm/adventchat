/**
 * AdventChat Widget — Full chat engine.
 *
 * Implements: WP-30 (shell), WP-31 (injection), WP-32 (session),
 * WP-33 (send), WP-34 (receive), WP-35 (typing), WP-36 (read receipts),
 * WP-37 (visitor info), WP-38 (sound).
 *
 * @package AdventChat
 */

(function () {
  'use strict';

  if (typeof window.adventchatConfig === 'undefined') return;

  var config = window.adventchatConfig;
  var settings = config.settings || {};
  var siteId = config.siteId;

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
  /*  WP-30/31: Build and inject widget DOM                              */
  /* ------------------------------------------------------------------ */
  function buildWidget() {
    var pos = settings.position || 'bottom-right';
    var root = document.createElement('div');
    root.className = 'adventchat-widget adventchat-widget--' + pos;
    root.style.setProperty('--ac-primary', settings.primaryColor || '#0066ff');
    root.style.setProperty('--ac-secondary', settings.secondaryColor || '#ffffff');

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
          '<textarea class="ac-input__text" id="ac-text" rows="1" placeholder="' + escAttr(settings.placeholder || 'Type a message\u2026') + '"></textarea>' +
          '<button class="ac-input__send" id="ac-send" type="button">' + ICON_SEND + '</button>' +
        '</div>' +
        '<div class="ac-powered">Powered by <a href="https://adventchat.com" target="_blank" rel="noopener">AdventChat</a></div>' +
      '</div>' +
      '<button class="ac-launcher" id="ac-launcher" type="button" aria-label="Toggle chat">' +
        '<span class="ac-icon-chat">' + ICON_CHAT + '</span>' +
        '<span class="ac-icon-close">' + ICON_CLOSE + '</span>' +
        '<span class="ac-badge" id="ac-badge">0</span>' +
      '</button>';

    document.body.appendChild(root);
    bindEvents(root);
  }

  function buildPrechatForm() {
    if (settings.prechatEnabled !== '1') return '';
    var html =
      '<div class="ac-prechat" id="ac-prechat">' +
        '<div class="ac-prechat__field">' +
          '<label class="ac-prechat__label" for="ac-name">Name</label>' +
          '<input class="ac-prechat__input" id="ac-name" type="text" required />' +
        '</div>' +
        '<div class="ac-prechat__field">' +
          '<label class="ac-prechat__label" for="ac-email">Email</label>' +
          '<input class="ac-prechat__input" id="ac-email" type="email" required />' +
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

    db.collection('sessions').add(sessionData).then(function (docRef) {
      state.sessionId = docRef.id;
      sessionStorage.setItem('adventchat_session', docRef.id);

      addSystemMessage('Chat started. An agent will be with you shortly.');

      listenForMessages();
      listenForAgentTyping();
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
      el.innerHTML = '<span>' + escHtml(msg.text) + '</span>';

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
