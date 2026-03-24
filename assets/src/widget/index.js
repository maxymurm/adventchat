/**
 * AdventChat Widget — Entry point.
 *
 * Vanilla JS chat widget, zero external dependencies.
 * Loaded on the WordPress frontend via wp_enqueue_script.
 *
 * @package AdventChat
 */

(function () {
  'use strict';

  // Placeholder — will be implemented in Phase 3 (WP-30+).
  if (typeof window.adventchatConfig === 'undefined') {
    return;
  }

  console.log('[AdventChat] Widget loaded', window.adventchatConfig.siteId);
})();
