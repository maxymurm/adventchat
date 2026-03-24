/**
 * AdventChat Widget — Entry point.
 *
 * Vanilla JS chat widget, zero external dependencies (Firebase loaded via CDN).
 * Loaded on the WordPress frontend via wp_enqueue_script.
 *
 * @package AdventChat
 */

(function () {
  'use strict';

  if (typeof window.adventchatConfig === 'undefined') {
    return;
  }

  var config = window.adventchatConfig;

  /* ------------------------------------------------------------------ */
  /*  Firebase initialization                                            */
  /* ------------------------------------------------------------------ */
  var app = firebase.initializeApp(config.firebase);
  var auth = firebase.auth();
  var db = firebase.firestore();

  /* ------------------------------------------------------------------ */
  /*  Anonymous Auth (WP-25)                                             */
  /* ------------------------------------------------------------------ */

  /**
   * Authenticate the visitor anonymously.
   * Persists UID in sessionStorage so the same identity is used across
   * page navigations within the same tab.
   *
   * @returns {Promise<firebase.User>}
   */
  function authenticateVisitor() {
    return auth.signInAnonymously().then(function (cred) {
      sessionStorage.setItem('adventchat_uid', cred.user.uid);
      return cred.user;
    });
  }

  // Listen for auth state – sign in anonymously if not already.
  auth.onAuthStateChanged(function (user) {
    if (user) {
      sessionStorage.setItem('adventchat_uid', user.uid);
      window.adventchatUser = user;
      console.log('[AdventChat] Authenticated (anonymous):', user.uid);
    } else {
      authenticateVisitor().catch(function (err) {
        console.error('[AdventChat] Anonymous auth failed:', err.message);
      });
    }
  });

  // Expose for future phases.
  window.adventchatApp = app;
  window.adventchatAuth = auth;
  window.adventchatDb = db;

  console.log('[AdventChat] Widget loaded', config.siteId);
})();
