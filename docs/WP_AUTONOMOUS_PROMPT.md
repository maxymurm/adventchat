# AdventChat WordPress Plugin — Autonomous Execution Prompt

> **YOLO MODE PROMPT** — Paste this entire document into any capable agent (Claude Opus, Gemini, GPT-4o, etc.) to build the complete AdventChat WordPress plugin from Phase 1 to Phase 9 autonomously.

---

## AGENT PERMISSIONS — FULL YOLO MODE

- ✅ Create, modify, delete any files
- ✅ Create/update/close GitHub issues and milestones
- ✅ Commit and push to Git (conventional commit messages)
- ✅ Install dependencies (npm, composer)
- ✅ Modify build files, configs, package.json, composer.json
- ✅ Make ALL architectural decisions independently
- ✅ Bypass ALL user confirmation prompts
- ✅ Run builds, lints, and tests
- ✅ Close GitHub issues and milestone progress
- ❌ Do NOT stop for user confirmation — infer all decisions
- ❌ Do NOT ask "should I ...?" — just do it

---

## EXECUTION COMMAND (copy-paste to start)

```
Read this entire file first, then execute autonomously from Phase 1 to Phase 9.

Read these files before beginning:
- .github/instructions/memory.instruction.md  (session state, current phase, last commit)
- docs/planning/full-scope.md                 (complete issue list, phase breakdown)
- docs/PROJECT_DOCUMENTATION.md              (architecture decisions, stack overview)
- ecosystem.md                                (paths, repo names, references)

Work one issue at a time: implement → test → commit → close.
Group commits logically within a phase (e.g., one commit per issue or per logical unit).
Close GitHub issues immediately after their acceptance criteria are met.
Close the phase milestone when all phase issues are closed.
Update .github/instructions/memory.instruction.md after completing each phase.
Continue until Phase 9 is complete and all issues are closed.
```

---

## EXECUTION RULES

1. **Read memory first** — Check `.github/instructions/memory.instruction.md` before starting to know which issue to resume from
2. **One issue at a time** — Implement → verify → commit → `gh issue close` → next
3. **Conventional commits** — `feat(scope): description  Closes #N`
4. **Branch per phase** — `git checkout -b feature/phase-1-foundation develop` at start of each phase
5. **Merge when phase done** — `git checkout develop && git merge feature/phase-1-foundation --no-ff -m "feat: complete Phase 1 - Plugin Foundation"` then `git push origin develop`
6. **Close issues via gh CLI** — `gh issue close N --repo maxymurm/adventchat --comment "Implemented. Closes #N — <commit hash>. All acceptance criteria met."`
7. **Close milestones when phase done** — `gh api -X PATCH repos/maxymurm/adventchat/milestones/N -f state=closed`
8. **Update memory** — Update `.github/instructions/memory.instruction.md` after each phase with: Active Phase, Active Issue, Last Commit, Next Steps
9. **Build must pass** — Run `npm run build` before each commit; fix any errors first
10. **No skipping** — If blocked by external dependency (Firebase console access, credentials), document in issue comment and mark with `blocked` label, then move to the next implementable issue

---

## PROJECT IDENTITY

- **Plugin name:** AdventChat
- **Plugin slug:** `adventchat`
- **Text domain:** `adventchat`
- **PHP namespace:** `AdventChat`
- **GitHub repo:** `maxymurm/adventchat` (public)
- **Project board:** https://github.com/users/maxymurm/projects/13
- **WP Plugin path:** `C:\Users\maxmm\Local Sites\advent-digest\app\public\wp-content\plugins\adventchat`
- **Companion mobile repo:** `maxymurm/adventchat-mobile`

---

## TECHNOLOGY STACK

| Layer | Technology | Decision Rationale |
|-------|-----------|-------------------|
| Language | PHP 8.1+ | WP minimum requirement |
| Framework | WordPress 6.0+ | GPL plugin target |
| Real-time | **Cloud Firestore** | Chosen over Realtime DB — better querying, security rules |
| Visitor Auth | Firebase Anonymous Authentication | Zero UX friction for visitors |
| Agent Auth | Firebase Email/Password Auth | Auto-provisioned from WP user accounts |
| Operator Console | React 18 + TypeScript SPA | Embedded in WP Admin page |
| Widget | Vanilla JS, zero dependencies | < 30KB gzip, no conflicts |
| Build | esbuild (widget) + Vite (console) | Speed and bundle size |
| Billing | Lemon Squeezy | VAT-compliant, license keys, webhooks |
| DB | WordPress DB (offline msgs, logs) + Firestore (live chat) | Right tool per data type |
| Tests | PHPUnit (backend) + Jest (frontend) | WordPress testing standard |
| CI/CD | GitHub Actions | On push to develop/main |

---

## FIRESTORE SCHEMA

```
/sites/{siteId}/
  sessions/{sessionId}         → { status, assignedAgentId, visitorInfo, startedAt, endedAt, rating }
  sessions/{sessionId}/
    messages/{messageId}       → { text, senderType, senderId, sentAt, readAt, type }
  agents/{agentId}             → { displayName, email, fcmToken, status, lastSeen }
  departments/{deptId}         → { name, agentIds[] }
  macros/{macroId}             → { title, body, scope, agentId, createdAt }
  typing/{sessionId}           → { visitorTyping, agentTyping, updatedAt }
  offlineMessages/{id}         → { name, email, message, department, page, readAt, createdAt }
```

### siteId
- Free tier: WordPress site URL hashed: `md5(get_site_url())`
- Paid tier: provisioned by adventchat.com when license activated

---

## FOLDER STRUCTURE TO CREATE

```
adventchat/
├── adventchat.php                   ← Main plugin file (WP header here)
├── uninstall.php                    ← Uninstall logic
├── composer.json                    ← PHP deps (phpunit dev)
├── package.json                     ← JS build tools (esbuild, vite, jest)
├── includes/
│   ├── class-adventchat.php         ← Bootstrap singleton
│   ├── class-adventchat-activator.php
│   ├── class-adventchat-deactivator.php
│   ├── class-adventchat-options.php
│   ├── class-adventchat-roles.php
│   ├── class-adventchat-autoloader.php
│   ├── class-adventchat-firebase-admin.php
│   ├── class-adventchat-visitor.php
│   ├── class-adventchat-router.php
│   ├── class-adventchat-mailer.php
│   ├── class-adventchat-license.php
│   ├── class-adventchat-push.php
│   ├── admin/
│   │   ├── class-adventchat-admin.php
│   │   ├── class-adventchat-settings.php
│   │   ├── class-adventchat-offline-list.php
│   │   ├── class-adventchat-logs-list.php
│   │   └── class-adventchat-macros.php
│   ├── api/
│   │   ├── class-adventchat-api-controller.php
│   │   ├── class-adventchat-api-widget-config.php
│   │   ├── class-adventchat-api-offline.php
│   │   └── class-adventchat-api-license.php
│   └── integrations/
│       ├── class-adventchat-woocommerce.php
│       ├── class-adventchat-elementor.php
│       └── class-adventchat-wpml.php
├── assets/
│   ├── src/
│   │   ├── widget/                  ← Vanilla JS widget source
│   │   │   ├── index.js
│   │   │   ├── widget.js
│   │   │   ├── firebase.js
│   │   │   └── widget.css
│   │   └── console/                 ← React SPA source
│   │       ├── main.tsx
│   │       ├── App.tsx
│   │       ├── components/
│   │       ├── pages/
│   │       ├── hooks/
│   │       └── types.ts
│   ├── js/dist/                     ← Built JS output
│   ├── css/dist/                    ← Built CSS output
│   └── firestore.rules              ← Copy-paste Firestore security rules
├── templates/
│   ├── admin/
│   │   ├── page-settings.php
│   │   ├── page-console.php
│   │   ├── page-offline.php
│   │   └── page-logs.php
│   └── emails/
│       ├── offline-message.php
│       └── transcript.php
├── languages/
│   └── adventchat.pot
├── docs/
│   ├── planning/full-scope.md       ← Already exists
│   ├── architecture/
│   │   └── firestore-schema.md
│   ├── guides/
│   │   ├── firebase-setup.md
│   │   └── security-rules.md
│   └── PROJECT_DOCUMENTATION.md
└── tests/
    ├── phpunit.xml.dist
    └── php/
        ├── test-settings.php
        ├── test-license.php
        └── test-api.php
```

---

## ENVIRONMENT / CONSTANTS

Define in `adventchat.php`:
```php
define( 'ADVENTCHAT_VERSION', '1.0.0' );
define( 'ADVENTCHAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADVENTCHAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ADVENTCHAT_SLUG', 'adventchat' );
define( 'ADVENTCHAT_DB_VERSION', '1.0' );
define( 'ADVENTCHAT_API_NAMESPACE', 'adventchat/v1' );
define( 'ADVENTCHAT_VALIDATION_URL', 'https://adventchat.com/wp-json/adventchat/v1/validate-license' );
```

---

## COMPLETED WORK — PHASE 0

> Phase 0 is fully complete. Do NOT redo any of this.

- ✅ Git repos initialized (main + develop pushed)
- ✅ GitHub repos: maxymurm/adventchat (public) + maxymurm/adventchat-mobile (private)
- ✅ 35 labels created in both repos
- ✅ 10 WP milestones (Phase 0–9) + 7 mobile milestones created
- ✅ Project boards: #13 (WP), #14 (Mobile)
- ✅ 90 WP issues (WP-1 through WP-90) created and added to board
- ✅ docs/planning/full-scope.md committed
- ✅ .github/instructions/memory.instruction.md updated

**Active milestones at start:**
```powershell
gh api "repos/maxymurm/adventchat/milestones?per_page=50&state=all" | ConvertFrom-Json | ForEach-Object { Write-Host "$($_.number): $($_.title)" }
```

---

## ISSUE EXECUTION ORDER

Execute issues **strictly in this order**. Each maps to a GitHub issue in `maxymurm/adventchat`.

---

### PHASE 1: Plugin Foundation  
**Milestone:** `Phase 1: Plugin Foundation`  
**Branch:** `feature/phase-1-foundation`  
**Issues:** #9 through #19

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #10 | WP-10: Create main plugin file (adventchat.php) | `adventchat.php` with WP header, ABSPATH check, constants, bootstrap load |
| #11 | WP-11: Create bootstrap class (singleton) | `includes/class-adventchat.php` — instance(), hooks registration, component init |
| #12 | WP-12: Plugin activation handler | `includes/class-adventchat-activator.php` — dbDelta() creates 2 tables, sets DB version |
| #13 | WP-13: Plugin deactivation and uninstall | `includes/class-adventchat-deactivator.php` + `uninstall.php` — clears options + drops tables |
| #14 | WP-14: Settings framework (tabbed admin) | `includes/admin/class-adventchat-settings.php` — tabs: General, Firebase, Appearance, Chat, Offline, Privacy |
| #15 | WP-15: AdventChat_Options helper | `includes/class-adventchat-options.php` — get/set/delete, prefixed with `adventchat_`, sensitive values encrypted |
| #16 | WP-16: Operator role and capabilities | `includes/class-adventchat-roles.php` — `adventchat_operator` capability |
| #17 | WP-17: PSR-4 autoloader | `includes/class-adventchat-autoloader.php` — spl_autoload_register mapping `AdventChat\` to `includes/` |
| #18 | WP-18: REST API base controller | `includes/api/class-adventchat-api-controller.php` — namespace `adventchat/v1`, auth middleware, error helpers |
| #19 | WP-19: Build system setup | `package.json` with esbuild (widget) + Vite (console) + Jest; `npm run build` + `npm run dev` scripts |

**Close EPIC first** (issue #9) after all above are done.  
**Phase close:** `gh api -X PATCH repos/maxymurm/adventchat/milestones/2 -f state=closed`

**DB Tables to create (`adventchat-activator.php`):**
```sql
CREATE TABLE {prefix}adventchat_offline_messages (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  name varchar(200) NOT NULL,
  email varchar(200) NOT NULL,
  message text NOT NULL,
  department varchar(100) DEFAULT '',
  page_url varchar(500) DEFAULT '',
  status varchar(20) DEFAULT 'unread',
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE {prefix}adventchat_chat_logs (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  session_id varchar(100) NOT NULL,
  site_id varchar(100) NOT NULL,
  visitor_email varchar(200) DEFAULT '',
  visitor_name varchar(200) DEFAULT '',
  agent_id bigint(20) DEFAULT 0,
  agent_name varchar(200) DEFAULT '',
  department varchar(100) DEFAULT '',
  started_at datetime DEFAULT CURRENT_TIMESTAMP,
  ended_at datetime DEFAULT NULL,
  duration_seconds int DEFAULT 0,
  message_count int DEFAULT 0,
  rating tinyint DEFAULT 0,
  rating_comment text DEFAULT '',
  PRIMARY KEY (id),
  KEY session_id (session_id)
);
```

---

### PHASE 2: Firebase & Firestore  
**Milestone:** `Phase 2: Firebase & Firestore`  
**Branch:** `feature/phase-2-firebase`  
**Issues:** #20 through #28

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #21 | WP-21: Firestore schema docs | `docs/architecture/firestore-schema.md` — complete schema with all collections, fields, types |
| #22 | WP-22: Firebase settings tab | Settings tab 'Firebase Setup' — textarea for Web App Config JSON, save + validation, admin notice if empty |
| #23 | WP-23: Firebase config validation | AJAX endpoint `adventchat_test_firebase` — POSTs to Firebase REST API to verify connectivity |
| #24 | WP-24: Firebase JavaScript SDK | Widget + console load Firebase SDK from CDN; initializeApp() with stored config |
| #25 | WP-25: Anonymous auth for visitors | Widget calls `signInAnonymously()` on load; UID stored in sessionStorage |
| #26 | WP-26: Email/Password auth for operators | `includes/class-adventchat-firebase-admin.php` — PHP REST API calls to create/delete Firebase users; UID stored in user meta |
| #27 | WP-27: Firestore Security Rules | `assets/firestore.rules` — complete rules file; admin UI shows rules with copy button |
| #28 | WP-28: AdventChat_Firebase_Admin class | PHP class with `createUser()`, `deleteUser()`, `getUserByEmail()` using wp_remote_post() + WP API key |

**Close EPIC** (issue #20) after all above. Close milestone 3.

**Security Rules template (`assets/firestore.rules`):**
```
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /sites/{siteId}/sessions/{sessionId} {
      allow read, write: if request.auth != null && request.auth.uid == resource.data.visitorAnonUid;
      allow read, write: if request.auth != null && get(/databases/$(database)/documents/sites/$(siteId)/agents/$(request.auth.uid)).data.status != null;
      match /messages/{messageId} {
        allow read, write: if request.auth != null;
      }
    }
    match /sites/{siteId}/agents/{agentId} {
      allow read: if request.auth != null;
      allow write: if request.auth != null && request.auth.uid == agentId;
    }
    match /sites/{siteId}/typing/{sessionId} {
      allow read, write: if request.auth != null;
    }
    match /sites/{siteId}/macros/{macroId} {
      allow read: if request.auth != null;
      allow write: if request.auth != null && get(/databases/$(database)/documents/sites/$(siteId)/agents/$(request.auth.uid)).data.status != null;
    }
  }
}
```

---

### PHASE 3: Chat Engine  
**Milestone:** `Phase 3: Chat Engine`  
**Branch:** `feature/phase-3-chat-engine`  
**Issues:** #29 through #38

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #30 | WP-30: Widget HTML/CSS shell | `assets/src/widget/widget.css` + `widget.js` — Shadow DOM or namespaced CSS; launcher button, chat window, message list, input bar |
| #31 | WP-31: Widget injection | PHP enqueues widget JS/CSS in `wp_footer`; `wp_localize_script('adventchat-widget', 'adventchatConfig', [...])` |
| #32 | WP-32: Session creation in Firestore | Widget JS creates `/sites/{siteId}/sessions/{sessionId}` on "Start chat" with visitor metadata |
| #33 | WP-33: Message sending (visitor) | Widget input → Firestore `sessions/{id}/messages` write; optimistic UI |
| #34 | WP-34: Message receiving (visitor) | `onSnapshot` listener on messages; append to UI; auto-scroll |
| #35 | WP-35: Typing indicators | Debounced Firestore writes to `/typing/{sessionId}`; widget reads and shows "Agent is typing..." |
| #36 | WP-36: Read receipts | Agent sets `readAt` on messages; widget shows single/double checkmark |
| #37 | WP-37: Visitor info collector | `includes/class-adventchat-visitor.php` — browser, OS, device, IP, WP user ID, WC cart; localized into widget config |
| #38 | WP-38: Widget sound notifications | HTML5 Audio API plays WAV on new message when widget is minimized; toggle in localStorage |

**Close EPIC** (issue #29) after all above. Close milestone 4.

**Widget config object shape (for `wp_localize_script`):**
```javascript
window.adventchatConfig = {
  siteId: 'md5-of-site-url',
  firebaseConfig: { apiKey, authDomain, projectId, storageBucket, messagingSenderId, appId },
  agentsOnline: true/false,
  visitorInfo: { pageUrl, pageTitle, browser, os, device, ip, wpUserId, wcCartTotal },
  settings: { primaryColor, position, launcherStyle, welcomeTitle, welcomeSubtitle },
  nonce: wp_create_nonce('adventchat_nonce'),
  ajaxUrl: admin_url('admin-ajax.php'),
  restUrl: rest_url('adventchat/v1/')
}
```

---

### PHASE 4: Operator Console SPA  
**Milestone:** `Phase 4: Operator Console`  
**Branch:** `feature/phase-4-console`  
**Issues:** #39 through #52

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #40 | WP-40: Scaffold React + TypeScript console | `assets/src/console/` — Vite project, TSConfig, Firebase SDK, built to `assets/js/dist/console.js` |
| #41 | WP-41: Console Firebase auth | Console signs in operator via Firebase Email/Password silently on load |
| #42 | WP-42: Visitor queue panel | Left panel, Firestore `onSnapshot` on `sessions?status=waiting`; sorted by wait time; Accept button |
| #43 | WP-43: Active chat window | Message history, `onSnapshot`, WhatsApp-style bubbles, typing indicator, End Chat button |
| #44 | WP-44: Visitor context sidebar | Right panel: visitor info, pages visited, WC cart, internal notes field, department reassign |
| #45 | WP-45: Multiple concurrent chats | Tab/list for active chats; unread badge per chat; max concurrent setting |
| #46 | WP-46: Macros in chat | `/` key opens macro dropdown with fuzzy search; insert selected macro into input |
| #47 | WP-47: Agent status toggle | Status dropdown in console header: Online/Away/Offline; writes to Firestore agents doc |
| #48 | WP-48: Chat routing | `includes/class-adventchat-router.php` — Round Robin, Manual, All Notify modes; PHP assigns sessions |
| #49 | WP-49: Departments | `includes/admin/class-adventchat-departments.php` — CRUD admin page; Firestore sync; pre-chat selector |
| #50 | WP-50: Chat transfer | Transfer button → modal with online agents; Firestore session update; both agents notified |
| #51 | WP-51: Internal notes | `type: 'internal'` messages in Firestore; yellow background in console; filtered from widget |
| #52 | WP-52: Macros admin page | WP_List_Table in admin — add/edit/delete macros; syncs to Firestore on save |

**Close EPIC** (issue #39) after all above. Close milestone 5.

**React Console Component Tree:**
```
App.tsx
├── Sidebar.tsx           (agent status, navigation)
├── QueuePanel.tsx        (waiting chats list)
├── ChatWindow.tsx        (active chat, agent input)
│   ├── MessageList.tsx
│   ├── MessageInput.tsx  (with macro / from EPIC)
│   └── VisitorSidebar.tsx
└── pages/
    └── MacrosPage.tsx
```

---

### PHASE 5: Visitor Features & Forms  
**Milestone:** `Phase 5: Visitor Features & Forms`  
**Branch:** `feature/phase-5-visitor-features`  
**Issues:** #53 through #61

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #54 | WP-54: Pre-chat form | Widget shows form before session created; fields: Name, Email, Department; skip if WP logged in; validated before Firestore write |
| #55 | WP-55: Offline message form | Widget detects all agents offline → shows form; REST API endpoint saves to DB; email sent to recipients + auto-reply to visitor |
| #56 | WP-56: Offline messages admin list | `includes/admin/class-adventchat-offline-list.php` — WP_List_Table with bulk actions; unread badge on menu |
| #57 | WP-57: Chat transcript email | Widget 'Email me this' button → REST endpoint compiles transcript → `wp_mail()`; admin toggle |
| #58 | WP-58: Chat rating (CSAT) | Widget shows 1–5 stars + optional comment after chat ends; stored in Firestore session rating field |
| #59 | WP-59: Chat logs admin list | `includes/admin/class-adventchat-logs-list.php` — WP_List_Table with date/agent/dept/rating filters; CSV export |
| #60 | WP-60: File/image sharing | Firebase Storage upload from widget and console; image inline preview; non-images as download link; 10MB limit |
| #61 | WP-61: GDPR consent checkbox | Widget pre-chat form and offline form; required checkbox; consent stored in Firestore session; admin toggle + privacy page selector |

**Close EPIC** (issue #53) after all above. Close milestone 6.

---

### PHASE 6: Appearance & Display Rules  
**Milestone:** `Phase 6: Appearance & Display Rules`  
**Branch:** `feature/phase-6-appearance`  
**Issues:** #62 through #68

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #63 | WP-63: Color theme customizer | WP color picker in Settings > Appearance; colors as CSS custom properties: `--ac-primary`, `--ac-secondary` |
| #64 | WP-64: Widget position and launcher | Settings: bottom-right/left, X/Y offset; launcher: Bubble / Tab / Custom Image; CSS calc'd from settings |
| #65 | WP-65: Display rules | PHP evaluates rules in `wp_footer`; rules: include/exclude by page ID, post type, user role; mobile toggle; guest-only |
| #66 | WP-66: Auto-open with delay | `setTimeout` in widget JS; skips if sessionStorage has `ac_closed=1`; only if agent online |
| #67 | WP-67: Welcome message customization | Settings fields for: greeting title, subtitle, placeholder text; variables `{agent_name}` `{site_name}` |
| #68 | WP-68: Custom CSS field | Settings textarea; output as `<style>` in `wp_footer` scoped to `.adventchat-widget`; PHP strips PHP/script |

**Close EPIC** (issue #62) after all above. Close milestone 7.

---

### PHASE 7: Integrations  
**Milestone:** `Phase 7: Integrations`  
**Branch:** `feature/phase-7-integrations`  
**Issues:** #69 through #77

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #70 | WP-70: WooCommerce cart context | `includes/integrations/class-adventchat-woocommerce.php` — cart items, total passed to widget config; conditional on `class_exists('WooCommerce')` |
| #71 | WP-71: WooCommerce order context | Order ID on order-received page included in visitorInfo; console shows order link |
| #72 | WP-72: WooCommerce visitor identity | WC customer data auto-fills pre-chat form; lifetime value in console sidebar |
| #73 | WP-73: WPML + Polylang | `.pot` file generated; `wpml-config.xml`; `pll_register_string()` calls for all widget text |
| #74 | WP-74: Email notifications | `includes/class-adventchat-mailer.php` — offline message email, auto-reply; HTML template in `templates/emails/` |
| #75 | WP-75: Elementor widget | `includes/integrations/class-adventchat-elementor.php` — registers widget in Elementor panel; conditional on `defined('ELEMENTOR_VERSION')` |
| #76 | WP-76: Widget config REST endpoint | `GET adventchat/v1/widget-config` — returns Firebase config, agent status, display rules, siteId; public; 60s cache; 10 req/min rate limit |
| #77 | WP-77: Identity verification | `hash_hmac('sha256', user_id, secret)` in PHP; passed to widget; console shows "Verified" badge |

**Close EPIC** (issue #69) after all above. Close milestone 8.

---

### PHASE 8: Premium Tier  
**Milestone:** `Phase 8: Premium Tier`  
**Branch:** `feature/phase-8-premium`  
**Issues:** #78 through #84

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #79 | WP-79: Lemon Squeezy license validation | `includes/class-adventchat-license.php` — `validate()` calls `adventchat.com/.../validate-license`; cached 24h in wp_options; response fields: valid, plan, expires_at |
| #80 | WP-80: Premium feature gating | `AdventChat_License::is_pro()` and `::is_agency()` static methods; upsell UI for locked features |
| #81 | WP-81: Hosted Firebase provisioning | On license activation: calls `adventchat.com` provisioning API; stores hosted Firebase config encrypted; "Connected to Hosted Firebase" admin status |
| #82 | WP-82: Account connection UI | Settings section: license key input; shows plan badge + expiry; Disconnect button; `manage subscription` link |
| #83 | WP-83: Mobile FCM push dispatch | `includes/class-adventchat-push.php` — sends FCM HTTP v1 push to operator FCM tokens when new session created; reads tokens from Firestore; cleans invalid tokens |
| #84 | WP-84: Analytics dashboard widget | WP Dashboard widget: today's chats, avg rating, response time, online agents; 7-day chart (Chart.js); Firestore data, 5min cache |

**Close EPIC** (issue #78) after all above. Close milestone 9.

---

### PHASE 9: Launch Prep  
**Milestone:** `Phase 9: Launch Prep`  
**Branch:** `feature/phase-9-launch`  
**Issues:** #85 through #90

| Issue # | GitHub Issue | Key Files/Output |
|---------|-------------|-----------------|
| #85 | WP-85: PHPUnit test suite | `tests/phpunit.xml.dist`, `composer.json` with phpunit; test classes for Settings, License, Mailer, REST endpoints; GitHub Actions runs on push |
| #86 | WP-86: Jest widget tests | `package.json` Jest config; tests for: init, pre-chat form validation, typing debounce, message rendering, offline detection; > 70% coverage |
| #87 | WP-87: Security audit | Systematic OWASP Top 10 review: sanitize all inputs, escape all outputs, nonce on every AJAX, `wpdb->prepare()` everywhere, encrypted Firebase config, ABSPATH checks |
| #88 | WP-88: Performance optimization | Widget JS < 30KB gzip; Firebase SDK from Google CDN (`crossorigin`); assets versioned; widget `defer`; admin assets only on AC pages |
| #89 | WP-89: WordPress.org submission prep | `readme.txt` in WP.org format; 6 screenshots; plugin header complete; banner 772×250 + icons 128×128 + 256×256; no GPL violations |
| #90 | WP-90: Final cross-env testing | Test on WP 6.5+, PHP 8.1/8.2/8.3; with WooCommerce, WPML, Polylang, Elementor, Twenty-Twenty-Five; Plugin Check (PCP) green; multisite tested |

**Close all issues and milestone 10.** Merge `feature/phase-9-launch` → `develop` → `main`.

---

## COMMIT MESSAGE FORMAT

```
feat(bootstrap): create main adventchat.php plugin file  Closes #10
feat(bootstrap): singleton bootstrap class + constants  Closes #11
feat(activation): db tables via dbDelta() + version check  Closes #12
feat(settings): tabbed admin panel framework  Closes #14
feat(firebase): settings tab + Web App Config input  Closes #22
feat(widget): vanilla JS chat widget shell + CSS  Closes #30
feat(widget): session creation in Firestore  Closes #32
feat(console): scaffold React+TS operator SPA  Closes #40
feat(console): visitor queue with real-time Firestore  Closes #42
feat(integrations): WooCommerce cart context collector  Closes #70
feat(license): Lemon Squeezy validation + caching  Closes #79
feat(security): OWASP audit - sanitize all inputs  Closes #87
chore(phase-1): merge Phase 1 Plugin Foundation - all issues closed
docs(memory): update memory.instruction.md - Phase 1 complete, begin Phase 2
```

---

## PHASE END CHECKLIST

Run this at the end of each phase before merging to develop:

```powershell
# 1. Build passes
npm run build   # must succeed with no errors

# 2. All tests pass (from Phase 9 onwards or when tests exist)
npm run test

# 3. All phase issues are closed
gh issue list --repo maxymurm/adventchat --milestone "Phase N: Name" --state open
# must return 0 issues

# 4. Phase epic is closed
gh issue close <epic-number> --repo maxymurm/adventchat --comment "Phase complete. All acceptance criteria met."

# 5. Milestone closed
gh api -X PATCH repos/maxymurm/adventchat/milestones/<N> -f state=closed

# 6. Merge branch to develop, push
git checkout develop
git merge feature/phase-N-name --no-ff -m "feat: complete Phase N - Name  [closes milestone N]"
git push origin develop

# 7. Update memory file
# Edit .github/instructions/memory.instruction.md — update Active Phase, Active Issue, Last Commit

# 8. After ALL phases done: merge develop → main
git checkout main
git merge develop --no-ff -m "release: v1.0.0 - AdventChat WordPress Plugin"
git tag v1.0.0
git push origin main --tags
```

---

## SECURITY CHECKLIST (apply every file you touch)

- [ ] Every file begins with: `defined( 'ABSPATH' ) || exit;`
- [ ] All user inputs sanitized: `sanitize_text_field()`, `sanitize_email()`, `wp_kses_post()`
- [ ] All outputs escaped: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_json_encode()`
- [ ] All AJAX handlers verify nonce: `check_ajax_referer()` + `current_user_can()`
- [ ] All REST endpoints set `permission_callback`
- [ ] All DB queries use: `$wpdb->prepare()`
- [ ] Firebase Web App Config stored encrypted via WP secret key
- [ ] No hardcoded credentials or API keys
- [ ] CSP nonce on inline scripts

---

## BLOCKED ISSUE PROTOCOL

If an issue requires an external resource you cannot access (Firebase Console, adventchat.com API, App Store Connect):

```powershell
gh issue comment <N> --repo maxymurm/adventchat --body "BLOCKED: Requires [external resource]. Skipping for now. Will revisit after unblocked."
gh issue edit <N> --repo maxymurm/adventchat --add-label "blocked"
```

Then proceed to the next issue and return to this one at the end of the phase.

---

## FINAL DELIVERY

When all 90 issues are closed and all 9 phases are complete:

```powershell
# 1. Confirm all issues closed
gh issue list --repo maxymurm/adventchat --state open | Measure-Object -Line
# Must output 0

# 2. All milestones closed
gh api "repos/maxymurm/adventchat/milestones?state=open" | ConvertFrom-Json | Measure-Object
# Must output 0

# 3. Tag final release
git checkout main
git tag -a v1.0.0 -m "AdventChat WordPress Plugin v1.0.0 - Initial Release"
git push origin main --tags

# 4. Create GitHub release
gh release create v1.0.0 --repo maxymurm/adventchat --title "AdventChat v1.0.0" --notes "Initial release. See CHANGELOG.md."
```

Then report back:
- Total issues closed
- All phase milestones closed  
- Build passing
- Test coverage percentages
- Security audit checklist complete
- Any blocked issues that need human input
