/**
 * AdventChat Operator Console — React SPA entry.
 *
 * Mounted in WP Admin on the AdventChat console page.
 */

import React from 'react';
import { createRoot } from 'react-dom/client';

function App() {
  return (
    <div id="adventchat-console">
      <h2>AdventChat Console</h2>
      <p>Operator console will be built in Phase 4.</p>
    </div>
  );
}

const container = document.getElementById('adventchat-console-root');
if (container) {
  createRoot(container).render(<App />);
}
