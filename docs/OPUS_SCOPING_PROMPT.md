# AdventChat — Opus Autonomous Scoping & Issue Creation Prompt

> **YOLO MODE PROMPT** — Paste this entire document into Claude Opus (or any capable agent) to autonomously initialize both GitHub repositories, create all project boards, labels, milestones, and ~120+ GitHub issues for the AdventChat WP plugin and mobile app.

---

## ⚡ AGENT PERMISSIONS — FULL YOLO MODE

- ✅ Create, modify, delete any files in project folders
- ✅ Run `git init`, `git add`, `git commit`, `git push`
- ✅ Create GitHub repos, labels, milestones, project boards, issues via `gh` CLI
- ✅ Run `npm`, `ionic`, `ng`, `pod`, `gradlew` commands
- ✅ Make ALL architectural decisions independently — context is fully provided
- ✅ Bypass ALL user confirmation prompts
- ✅ Write and commit planning documents
- ❌ Do NOT stop for user confirmation — infer everything from this document
- ❌ Do NOT ask "should I...?" — just do it
- ❌ Do NOT skip issues — create every single one in the list below

---

## 📋 EXECUTION COMMAND

```
Read this entire file first. Then execute every step from top to bottom autonomously.

Also read these files before beginning work:
- .github/instructions/memory.instruction.md           (WP plugin — project context + decisions)
- docs/PROJECT_DOCUMENTATION.md                        (WP plugin — phase + feature inventory)
- ecosystem.md                                          (WP plugin — tech stack + pricing)
- C:\Users\maxmm\projects\adventchat-mobile\docs\PROJECT_DOCUMENTATION.md
- C:\Users\maxmm\projects\adventchat-mobile\.github\instructions\memory.instruction.md

Also skim these reference plugin files for context (do NOT copy their code):
- _references/yith-live-chat/class-yith-livechat.php   (OOP architecture pattern)
- _references/yith-live-chat/includes/class-ylc-settings.php (settings admin pattern)
- _references/screets-chat/core/admin.php              (SPA console embedding pattern)
- _references/livechat/includes/plugin.php             (widget UX + WooCommerce pattern)

Work through all steps. Report what was accomplished and any blockers when done.
```

---

## 🔧 EXECUTION RULES

1. **Read memory first** — check `.github/instructions/memory.instruction.md` before each session
2. **One task at a time** — complete each step fully before moving to next
3. **Conventional commits** — `feat(scope): description (#N)` or `chore(setup): description`
4. **Push after each section** — `git push origin main` after completing each major step
5. **Close issues via gh** — `gh issue close N --repo REPO --comment "Implemented in commit abc123."`
6. **Update memory** — update `.github/instructions/memory.instruction.md` after each major section
7. **Report blockers** — if a step is blocked (missing credential, auth issue), document it and continue
8. **GitHub CLI** — use `gh` for all GitHub operations (repos, labels, milestones, issues, projects)

---

## 🗂️ PROJECT CONTEXT

### Two Repositories — You Are Setting Up BOTH

| | WP Plugin | Mobile App |
|--|-----------|------------|
| **Path** | `C:\Users\maxmm\Local Sites\advent-digest\app\public\wp-content\plugins\adventchat` | `C:\Users\maxmm\projects\adventchat-mobile` |
| **GitHub repo** | `maxymurm/adventchat` | `maxymurm/adventchat-mobile` |
| **Visibility** | Public (GPL) | Private (closed source) |
| **Type** | WordPress Plugin (PHP) | Ionic 8 + Angular 18 (Capacitor) |
| **Board name** | AdventChat WordPress Plugin | AdventChat Mobile App |

### What AdventChat Does
A freemium WordPress live chat plugin where:
- **Free tier:** Users bring their own Firebase/Cloud Firestore project — paste a 6-field Web App Config JSON, done
- **Paid tier (Pro $24/mo, Agency $59/mo):** AdventChat-hosted Firestore, mobile app access, white-label
- The **mobile app** (`adventchat-mobile`) is the paid tier's killer feature — operators manage chats from iOS/Android

### Key Architecture Decisions (do NOT change these)
- **Cloud Firestore** (NOT Realtime Database) — collections/subcollections, real-time listeners
- **Visitor auth:** Firebase Anonymous Authentication
- **Agent auth:** Firebase Email/Password (plugin auto-creates via Firebase Auth REST API)
- **Free tier setup:** User pastes Web App Config JSON only — NO service account key needed
- **Hosted Firestore (paid tier):** One shared Firebase project, partitioned by `siteId`
- **Subscription billing:** Lemon Squeezy (not WooCommerce Subscriptions)
- **No in-app purchase:** Mobile app is free download, users subscribe at adventchat.com
- **Operator console:** React + TypeScript SPA embedded in WP Admin
- **Visitor widget:** Vanilla JS, zero dependencies, responsive/mobile-first

### Firestore Data Structure
```
/sites/{siteId}/sessions/{sessionId}                  ← chat sessions
/sites/{siteId}/sessions/{sessionId}/messages/{id}    ← messages subcollection
/sites/{siteId}/agents/{agentId}                      ← online status, FCM tokens
/sites/{siteId}/departments/{deptId}                  ← department routing
/sites/{siteId}/macros/{macroId}                      ← canned responses
/sites/{siteId}/typing/{sessionId}                    ← ephemeral typing state
/sites/{siteId}/offlineMessages/{id}                  ← offline form submissions
```

---

## 🚀 STEP 1 — Initialize Git Repositories

### 1A: WP Plugin

```powershell
Set-Location "C:\Users\maxmm\Local Sites\advent-digest\app\public\wp-content\plugins\adventchat"

git init
git checkout -b main
git add .gitignore ecosystem.md docs/ .github/ agents/
git commit -m "chore(setup): initial project structure, docs, agent automation system"
```

### 1B: Mobile App

```powershell
Set-Location "C:\Users\maxmm\projects\adventchat-mobile"

git init
git checkout -b main
git add .gitignore ecosystem.md docs/ .github/ agents/
git commit -m "chore(setup): initial project structure, docs, agent automation system"
```

---

## 🐙 STEP 2 — Create GitHub Repositories

```powershell
# WP Plugin — PUBLIC
gh repo create maxymurm/adventchat `
  --public `
  --description "AdventChat — Firebase-powered live chat plugin for WordPress. Free with your own Firebase. Paid tier includes hosted Firebase and iOS/Android mobile app." `
  --license gpl-3.0

# Mobile App — PRIVATE  
gh repo create maxymurm/adventchat-mobile `
  --private `
  --description "AdventChat Mobile — Operator iOS/Android app for AdventChat WordPress Live Chat (paid tier)"

# Set remote origins
Set-Location "C:\Users\maxmm\Local Sites\advent-digest\app\public\wp-content\plugins\adventchat"
git remote add origin https://github.com/maxymurm/adventchat.git
git push -u origin main

Set-Location "C:\Users\maxmm\projects\adventchat-mobile"
git remote add origin https://github.com/maxymurm/adventchat-mobile.git
git push -u origin main

# Create develop branches
Set-Location "C:\Users\maxmm\Local Sites\advent-digest\app\public\wp-content\plugins\adventchat"
git checkout -b develop
git push -u origin develop
git checkout main

Set-Location "C:\Users\maxmm\projects\adventchat-mobile"
git checkout -b develop
git push -u origin develop
git checkout main
```

---

## 🏷️ STEP 3 — Create Labels (Both Repos)

Run these `gh label create` commands for **BOTH repos** (replace `REPO` with `maxymurm/adventchat` then `maxymurm/adventchat-mobile`):

```bash
# Phase labels
gh label create "phase-0" --color "ededed" --description "Phase 0: Project Setup" --repo REPO
gh label create "phase-1" --color "0075ca" --description "Phase 1: Foundation" --repo REPO
gh label create "phase-2" --color "0052cc" --description "Phase 2: Core Feature" --repo REPO
gh label create "phase-3" --color "003d99" --description "Phase 3: Advanced Features" --repo REPO
gh label create "phase-4" --color "002d73" --description "Phase 4: Integrations / Visitor UX" --repo REPO
gh label create "phase-5" --color "001f4d" --description "Phase 5: Appearance / Polish" --repo REPO
gh label create "phase-6" --color "001326" --description "Phase 6: Premium Tier" --repo REPO
gh label create "phase-7" --color "000000" --description "Phase 7: Launch Prep" --repo REPO

# Type labels
gh label create "enhancement" --color "a2eeef" --description "New feature or improvement" --repo REPO
gh label create "bug" --color "d73a4a" --description "Something isn't working" --repo REPO
gh label create "documentation" --color "0075ca" --description "Documentation update" --repo REPO
gh label create "setup" --color "e4e669" --description "Project setup and configuration" --repo REPO
gh label create "security" --color "ee0701" --description "Security-related" --repo REPO
gh label create "performance" --color "f9d0c4" --description "Performance improvement" --repo REPO
gh label create "testing" --color "bfd4f2" --description "Tests and QA" --repo REPO

# Layer labels
gh label create "backend" --color "c5def5" --description "PHP/server-side code" --repo REPO
gh label create "frontend" --color "fef2c0" --description "JavaScript/CSS/UI" --repo REPO
gh label create "mobile" --color "d4c5f9" --description "Ionic/Capacitor mobile app" --repo REPO
gh label create "firebase" --color "f9a825" --description "Firebase/Firestore related" --repo REPO
gh label create "database" --color "0e8a16" --description "Database schema/migration" --repo REPO
gh label create "api" --color "e11d48" --description "REST API related" --repo REPO
gh label create "woocommerce" --color "7b2fbe" --description "WooCommerce integration" --repo REPO

# Priority labels
gh label create "priority: critical" --color "b60205" --description "Must fix immediately" --repo REPO
gh label create "priority: high" --color "d93f0b" --description "High priority" --repo REPO
gh label create "priority: normal" --color "e4e669" --description "Normal priority" --repo REPO
gh label create "priority: low" --color "c2e0c6" --description "Low priority, nice to have" --repo REPO

# Tier labels
gh label create "free-tier" --color "28a745" --description "Available in free tier" --repo REPO
gh label create "paid-tier" --color "6f42c1" --description "Paid tier feature only" --repo REPO
gh label create "epic" --color "3e4b9e" --description "Epic (parent issue)" --repo REPO
```

---

## 🗓️ STEP 4 — Create Milestones (Both Repos)

```bash
# WP Plugin milestones
gh api repos/maxymurm/adventchat/milestones -f title="Phase 0: Project Setup" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 1: Plugin Foundation" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 2: Firebase & Firestore" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 3: Chat Engine" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 4: Operator Console" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 5: Visitor Features & Forms" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 6: Appearance & Display Rules" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 7: Integrations" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 8: Premium Tier" -f state="open"
gh api repos/maxymurm/adventchat/milestones -f title="Phase 9: Launch Prep" -f state="open"

# Mobile app milestones
gh api repos/maxymurm/adventchat-mobile/milestones -f title="Phase 0: Project Setup" -f state="open"
gh api repos/maxymurm/adventchat-mobile/milestones -f title="Phase 1: Foundation" -f state="open"
gh api repos/maxymurm/adventchat-mobile/milestones -f title="Phase 2: Chat Interface" -f state="open"
gh api repos/maxymurm/adventchat-mobile/milestones -f title="Phase 3: Push Notifications" -f state="open"
gh api repos/maxymurm/adventchat-mobile/milestones -f title="Phase 4: Operator Features" -f state="open"
gh api repos/maxymurm/adventchat-mobile/milestones -f title="Phase 5: Auth & Account" -f state="open"
gh api repos/maxymurm/adventchat-mobile/milestones -f title="Phase 6: Polish & App Stores" -f state="open"
```

---

## 📋 STEP 5 — Create GitHub Project Boards

```bash
# Get your GitHub user node ID first:
gh api graphql -f query='{ viewer { id login } }'
# Store the "id" value — you'll need it for the createProjectV2 mutation

# Create WP Plugin board
gh api graphql -f query='
mutation {
  createProjectV2(input: {
    ownerId: "USER_NODE_ID_HERE"
    title: "AdventChat WordPress Plugin"
  }) {
    projectV2 { id url }
  }
}'

# Create Mobile App board
gh api graphql -f query='
mutation {
  createProjectV2(input: {
    ownerId: "USER_NODE_ID_HERE"
    title: "AdventChat Mobile App"
  }) {
    projectV2 { id url }
  }
}'

# Note the project IDs/URLs — record them in ecosystem.md and memory.instruction.md
```

---

## 📝 STEP 6 — Create All Issues: WP Plugin (`maxymurm/adventchat`)

Use `gh issue create --repo maxymurm/adventchat --title "..." --body "..." --label "..." --milestone "..."` for each issue below.

Write full acceptance criteria in the `--body` for each issue. Use this body format:

```markdown
## Description
[1-2 sentence summary]

## Acceptance Criteria
- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Criterion 3

## Technical Notes
[Implementation hints if relevant]
```

---

### PHASE 0: Project Setup

**Issue WP-1: Verify GitHub CLI auth and global prerequisites**
- Labels: `setup, phase-0`
- Milestone: `Phase 0: Project Setup`
- Acceptance: gh CLI installed, authenticated as maxymurm, git config set

**Issue WP-2: Initialize Git repository for WP plugin**
- Labels: `setup, phase-0`
- Milestone: `Phase 0: Project Setup`
- Acceptance: git init done, main + develop branches, initial commit, pushed to origin

**Issue WP-3: Create GitHub repository (maxymurm/adventchat, public, GPL)**
- Labels: `setup, phase-0`
- Milestone: `Phase 0: Project Setup`
- Acceptance: Repo created, GPL-3.0 license, description set, remote configured

**Issue WP-4: Create GitHub project board with columns (Backlog, In Progress, In Review, Done)**
- Labels: `setup, phase-0`
- Milestone: `Phase 0: Project Setup`
- Acceptance: Project board created, linked to repo, URL recorded in ecosystem.md

**Issue WP-5: Create all GitHub labels (phase, type, priority, tier)**
- Labels: `setup, phase-0`
- Milestone: `Phase 0: Project Setup`
- Acceptance: All labels from STEP 3 created in repo

**Issue WP-6: Create all GitHub milestones (Phase 0–9)**
- Labels: `setup, phase-0`
- Milestone: `Phase 0: Project Setup`
- Acceptance: All 10 milestones exist

**Issue WP-7: Create initial docs/planning/full-scope.md planning document**
- Labels: `setup, phase-0, documentation`
- Milestone: `Phase 0: Project Setup`
- Acceptance: Comprehensive scope doc committed

**Issue WP-8: Create GitHub Issue Templates (bug_report.md, feature_request.md)**
- Labels: `setup, phase-0, documentation`
- Milestone: `Phase 0: Project Setup`
- Acceptance: Both templates committed to .github/ISSUE_TEMPLATE/

---

### PHASE 1: Plugin Foundation

**Issue WP-9: Epic — Plugin core architecture and bootstrap**
- Labels: `epic, phase-1, backend`
- Milestone: `Phase 1: Plugin Foundation`
- Description: Parent epic for all Phase 1 foundation work

**Issue WP-10: Create main plugin file (adventchat.php) with WordPress plugin header**
- Labels: `phase-1, enhancement, backend`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: Plugin header with Name, Description, Version (1.0.0), Author, License (GPL-3.0), Text Domain. Plugin recognized by WordPress.

**Issue WP-11: Create main AdventChat bootstrap class (singleton pattern)**
- Labels: `phase-1, enhancement, backend`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: `AdventChat` class, `instance()` singleton, all constants defined (`ADVENTCHAT_VERSION`, `ADVENTCHAT_PLUGIN_DIR`, `ADVENTCHAT_PLUGIN_URL`, `ADVENTCHAT_SLUG`). Activation/deactivation/uninstall hooks registered.

**Issue WP-12: Create plugin activation handler (DB table creation)**
- Labels: `phase-1, enhancement, backend, database`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: On activation: creates `{prefix}_adventchat_offline_messages` table, `{prefix}_adventchat_chat_logs` table, sets plugin version in options. Uses `dbDelta()`. Runs only when upgrading (version check).

**Issue WP-13: Create plugin deactivation and uninstall handlers**
- Labels: `phase-1, enhancement, backend`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: Deactivation: clears scheduled events. Uninstall: removes all plugin options, drops custom tables. Uninstall check via `WP_UNINSTALL_PLUGIN` constant.

**Issue WP-14: Create plugin settings framework (tabbed admin panel)**
- Labels: `phase-1, enhancement, backend, frontend`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: `AdventChat_Settings` class. WP admin menu item "AdventChat" with sub-pages: Settings, Console, Offline Messages, Chat Logs. Tabbed settings UI with tabs: General, Firebase, Appearance, Chat, Offline, Privacy. Settings saved via WP Options API. Settings page uses `settings_fields()` / `do_settings_sections()`.

**Issue WP-15: Create AdventChat_Options helper class**
- Labels: `phase-1, enhancement, backend`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: Static `get($key, $default)`, `set($key, $value)`, `delete($key)` methods wrapping `get_option`/`update_option` with `adventchat_` prefix. All sensitive values (Firebase config) encrypted at rest using WordPress secret key.

**Issue WP-16: Create custom WordPress operator role and capabilities**
- Labels: `phase-1, enhancement, backend`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: `adventchat_operator` capability added. Administrators have it by default. Settings to grant operator capability to other WP roles. `AdventChat_Roles` class. `current_user_can('adventchat_operator')` check used throughout.

**Issue WP-17: Create plugin autoloader (PSR-4 style)**
- Labels: `phase-1, enhancement, backend`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: `spl_autoload_register` based autoloader. All classes in `includes/` namespace `AdventChat\`. E.g. `AdventChat\Admin\Settings` → `includes/admin/class-adventchat-settings.php`.

**Issue WP-18: Create AdventChat REST API base (WP REST API v2)**
- Labels: `phase-1, enhancement, backend, api`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: `AdventChat\API\Controller` base class. Namespace `adventchat/v1`. All endpoints require authentication (nonce or JWT). Registers routes via `rest_api_init` hook.

**Issue WP-19: Setup build system for assets (npm + esbuild/webpack)**
- Labels: `phase-1, enhancement, frontend`
- Milestone: `Phase 1: Plugin Foundation`
- Acceptance: `package.json` with dev dependencies. `npm run build` produces minified assets to `assets/js/dist/` and `assets/css/dist/`. `npm run dev` starts watch mode. Separate bundles for: widget (visitor), console (operator SPA), admin (settings pages).

---

### PHASE 2: Firebase & Cloud Firestore

**Issue WP-20: Epic — Firebase integration and credential management**
- Labels: `epic, phase-2, firebase`
- Milestone: `Phase 2: Firebase & Firestore`

**Issue WP-21: Design and document Firestore data schema**
- Labels: `phase-2, firebase, documentation, database`
- Milestone: `Phase 2: Firebase & Firestore`
- Acceptance: `docs/architecture/firestore-schema.md` with all collections, field types, indexes, security rule requirements. Includes: sessions, messages, agents, departments, macros, typing, offlineMessages.

**Issue WP-22: Create Firebase settings tab (Web App Config input)**
- Labels: `phase-2, enhancement, backend, firebase, frontend`
- Milestone: `Phase 2: Firebase & Firestore`
- Acceptance: Settings tab "Firebase Setup". Single textarea for pasting Web App Config JSON (apiKey, authDomain, projectId, storageBucket, messagingSenderId, appId). OR individual fields for each. Admin notice shown if not configured. Step-by-step setup guide link. JSON validated on save (checks required fields). Config stored encrypted.

**Issue WP-23: Create Firebase Web App Config validation and test connection**
- Labels: `phase-2, enhancement, backend, firebase`
- Milestone: `Phase 2: Firebase & Firestore`
- Acceptance: AJAX endpoint `adventchat_test_firebase` — attempts to reach Firebase REST API using stored config. Returns success/error with specific message (wrong project ID, invalid API key, etc.). "Test Connection" button in settings that shows result inline.

**Issue WP-24: Integrate Firebase JavaScript SDK (client-side, widget + console)**
- Labels: `phase-2, enhancement, frontend, firebase`
- Milestone: `Phase 2: Firebase & Firestore`
- Acceptance: Firebase SDK loaded from CDN (not bundled — reduces plugin size). Only firebase/app, firebase/firestore, firebase/auth modules. Version pinned. Initialized with stored Web App Config. Error handling if SDK fails to load.

**Issue WP-25: Implement Firebase Anonymous Authentication for visitors**
- Labels: `phase-2, enhancement, frontend, firebase`
- Milestone: `Phase 2: Firebase & Firestore`
- Acceptance: Visitors sign in anonymously (`signInAnonymously`) when widget loads. UID stored in sessionStorage for session continuity. On tab/browser close, anonymous users are not deleted immediately (session can resume). Handles auth errors gracefully.

**Issue WP-26: Implement Firebase Email/Password auth for operators (auto-provision)**
- Labels: `phase-2, enhancement, backend, firebase`
- Milestone: `Phase 2: Firebase & Firestore`
- Acceptance: When a WordPress user is granted `adventchat_operator` capability: PHP calls Firebase Auth REST API to create a Firebase email/password account (using WP user email + generated secure password). Firebase UID stored in user meta. On WP user deletion: removes Firebase account via REST API. Operator signs in to console using these Firebase credentials transparently (hidden from user).

**Issue WP-27: Write Firestore Security Rules and provide admin copy-paste UI**
- Labels: `phase-2, enhancement, backend, firebase, security`
- Milestone: `Phase 2: Firebase & Firestore`
- Acceptance: Complete Firestore security rules that: allow anonymous users to write to their own session only, allow operators to read/write all sessions for their site, prevent cross-site data access. Rules stored in `assets/firestore.rules`. Admin settings shows rules in a code block with one-click copy button and link to Firebase Console rules editor.

**Issue WP-28: Create AdventChat_Firebase_Admin PHP class (server-side Firebase Auth REST)**
- Labels: `phase-2, enhancement, backend, firebase`
- Milestone: `Phase 2: Firebase & Firestore`
- Acceptance: PHP class using Firebase Auth REST API (no service account needed). Methods: `createUser($email, $password)`, `deleteUser($uid)`, `getUserByEmail($email)`. Uses `wp_remote_post()` with Firebase Auth REST endpoint and Web App API key. Handles HTTP errors.

---

### PHASE 3: Chat Engine

**Issue WP-29: Epic — Real-time chat engine**
- Labels: `epic, phase-3, firebase, frontend`
- Milestone: `Phase 3: Chat Engine`

**Issue WP-30: Design and build visitor chat widget HTML/CSS shell**
- Labels: `phase-3, enhancement, frontend`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: Vanilla JS widget. Renders: launcher button (bottom-right default), chat window (with header, messages area, input bar). Responsive (mobile-first). Smooth open/close animation. Widget isolated from page styles (Shadow DOM or strict CSS namespacing). No jQuery, no framework dependencies.

**Issue WP-31: Implement widget injection into WordPress frontend**
- Labels: `phase-3, enhancement, backend, frontend`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: Widget JS/CSS enqueued in `wp_footer`. Firebase config passed via `wp_localize_script` as `adventchatConfig` object. Widget only loaded when: plugin enabled, not excluded by display rules, Firebase configured. Widget not double-loaded. AJAX nonce included for offline form submissions.

**Issue WP-32: Implement chat session creation in Firestore**
- Labels: `phase-3, enhancement, frontend, firebase`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: When visitor opens chat and submits pre-chat form (or directly if form disabled): creates `/sites/{siteId}/sessions/{sessionId}` document with status "waiting", visitor info (page, browser, OS, IP via PHP-localized geo data), timestamp. Session ID stored in sessionStorage. Handles offline state (no agents online → offline form).

**Issue WP-33: Implement real-time message sending (visitor side)**
- Labels: `phase-3, enhancement, frontend, firebase`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: Text input with send button (Enter key shortcut). Message written to Firestore `/sessions/{id}/messages/{id}` as `{ text, senderType: 'visitor', senderId: anonUID, sentAt: serverTimestamp() }`. Optimistic UI (message shown immediately, flagged as pending until Firestore confirms). Input cleared and focused after send. Character limit (1000 chars).

**Issue WP-34: Implement real-time message receiving (visitor side)**
- Labels: `phase-3, enhancement, frontend, firebase`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: `onSnapshot` listener on session's messages subcollection. New messages appended with smooth scroll-to-bottom. Agent messages rendered with agent avatar. Agent name shown above message group. Timestamps formatted (e.g. "2:34 PM"). Reconnect handler if Firestore listener drops.

**Issue WP-35: Implement typing indicators (visitor ↔ agent)**
- Labels: `phase-3, enhancement, frontend, firebase`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: When typing, writes to `/sites/{siteId}/typing/{sessionId}` with 3s TTL debounce. Other party listens for changes and shows "Agent is typing..." / "Visitor is typing..." with animated dots. Typing status cleared when message sent or after 5s inactivity.

**Issue WP-36: Implement read receipts**
- Labels: `phase-3, enhancement, frontend, firebase`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: When agent reads a visitor message (chat window focused), marks `readAt` field on messages. Visitor widget shows "Read" or checkmark under agent-read messages (like WhatsApp). Shows single check (sent) vs double check (read).

**Issue WP-37: Build PHP visitor info collector (browser, OS, IP, geolocation)**
- Labels: `phase-3, enhancement, backend`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: `AdventChat_Visitor` class. Detects: browser name + version (User-Agent parsing), OS, device type (mobile/tablet/desktop), IP address (handles proxies: `HTTP_X_FORWARDED_FOR`), current page URL and title, WordPress user ID if logged in, WooCommerce cart total if active. Data localized into widget's JS config.

**Issue WP-38: Implement chat widget sound notifications**
- Labels: `phase-3, enhancement, frontend`
- Milestone: `Phase 3: Chat Engine`
- Acceptance: Plays a short notification sound when: new message arrives and widget is minimized. Uses HTML5 Audio API. Sound bundled as base64 or small WAV in assets. User can disable sounds (stored in localStorage). No autoplay on page load.

---

### PHASE 4: Operator Console

**Issue WP-39: Epic — Operator console SPA**
- Labels: `epic, phase-4, frontend`
- Milestone: `Phase 4: Operator Console`

**Issue WP-40: Scaffold React + TypeScript operator console SPA**
- Labels: `phase-4, enhancement, frontend`
- Milestone: `Phase 4: Operator Console`
- Acceptance: React 18 app in `assets/src/console/`. TypeScript. Vite or esbuild bundler. Built output to `assets/js/dist/console.js`. Loaded only on WP Admin "AdventChat → Console" page via WP `admin_enqueue_scripts`. Firebase SDK initialized. Console full-page within WP Admin (no sidebar overlap on mobile).

**Issue WP-41: Console — Firebase authentication (operator sign-in flow)**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Console checks if operator Firebase account exists (stored UID in user meta via REST). Auto-signs in with stored credentials (transparent, no login screen shown). If no credentials: shows "Connect" button that triggers auto-provisioning (Issue WP-26). Handles auth errors: Firebase account not found, wrong password, network error. Shows spinner while authenticating.

**Issue WP-42: Console — Visitor queue (waiting chats panel)**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Left panel showing all sessions with `status: "waiting"`. Real-time updates via Firestore listener. Each card shows: visitor name/email, current page URL, wait time, browser/OS icons, department badge. Sorted by wait time (oldest first). "Accept" button assigns session to current agent. Empty state: "No visitors waiting" with agent status toggle. Configurable: "Assign to me only" vs "Show all waiting".

**Issue WP-43: Console — Active chat window (WhatsApp-style)**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Right panel opens when chat accepted. Message history loaded from Firestore. New messages in real-time. Agent input with: send button, emoji picker, file attachment button, macro selector. Message bubbles: agent = right (primary color), visitor = left (gray). Timestamps. Typing indicator shown. Visitor avatar/initials. "End Chat" button with confirmation. Chat window scrolls to latest message on open.

**Issue WP-44: Console — Visitor context sidebar panel**
- Labels: `phase-4, enhancement, frontend`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Collapsible right-side panel in chat window showing: visitor name + email, current page URL (clickable), all pages visited in this session, browser + OS + device type icons, IP address + country flag, WooCommerce cart items + total (if active), chat duration, previous chat sessions count. Department selector (reassign). Notes field (internal).

**Issue WP-45: Console — Multiple concurrent chat management**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Console supports multiple active chats simultaneously. Chat tabs or list in left panel shows all agent's active sessions. Unread message count badge per chat. Clicking switches active chat window. New chat requests shown as notification inside console (badge + sound). Max concurrent chats setting in admin (default: 3).

**Issue WP-46: Console — Canned responses (macros) in chat**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Typing `/` in chat input opens macro search dropdown. Macros listed by title. Fuzzy search as user types after `/`. Selecting a macro inserts text into input (editable before sending). Macros loaded from Firestore `/sites/{siteId}/macros/`. Agent-specific macros + shared macros shown separately. "Add new macro" quick link.

**Issue WP-47: Console — Agent online/away/offline status toggle**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Status dropdown in console header. Options: Online (green), Away (yellow), Offline (red). Status written to Firestore `/sites/{siteId}/agents/{agentId}/status`. Widget checks for any online agent — if none, shows offline form instead of chat. Last-seen timestamp updated every 60s when online. Status auto-set to offline if browser tab closed.

**Issue WP-48: Console — Chat routing: round-robin and manual assignment**
- Labels: `phase-4, enhancement, backend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Admin setting: routing mode (Round Robin / Manual / All Notify). Round Robin: assigns new session to online agent with fewest active chats. Manual: session sits in queue until an agent explicitly accepts it. All Notify: all online agents see new chats, first to accept wins. `AdventChat_Router` PHP class queued via `wp_cron` checks + REST API. Assignment written to Firestore session document.

**Issue WP-49: Console — Departments and routing by department**
- Labels: `phase-4, enhancement, backend, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: Admin: create/edit/delete departments (name, agents assigned). Visitor pre-chat form optionally shows department selector. New sessions routed to agents in selected department only. Console queue filterable by department. Agent sees their department's queue. `AdventChat_Departments` class manages CRUD. Data synced to Firestore `/sites/{siteId}/departments/`.

**Issue WP-50: Console — Chat transfer between agents**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: "Transfer" button in chat window. Modal shows list of online agents (with active chat count). Transfer updates Firestore session `assignedAgentId`. Receiving agent gets notification. Visitor notified: "You've been transferred to [Agent Name]". Transfer log appended to chat as system message.

**Issue WP-51: Console — Internal chat notes (not visible to visitor)**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Console`
- Acceptance: "Add Note" button in chat. Notes stored in Firestore messages subcollection with `type: "internal"`. Rendered in console with distinctive style (yellow background, "INTERNAL NOTE" badge). Notes NEVER sent to visitor widget (widget only queries `type != "internal"`). Notes included in chat transcript downloads (admin only).

**Issue WP-52: Admin — Macros management page (WP Admin CRUD)**
- Labels: `phase-4, enhancement, backend, frontend`
- Milestone: `Phase 4: Operator Console`
- Acceptance: WP Admin "AdventChat → Macros" sub-page. WP_List_Table showing all macros (title, body preview, agent/shared, date). Add/Edit form with title + body (textarea with placeholder suggestions). Delete with confirmation. Macros sync to Firestore on save/delete. Shortcode-style variables: `{visitor_name}`, `{agent_name}`, `{site_name}`.

---

### PHASE 5: Visitor Features & Forms

**Issue WP-53: Epic — Visitor-facing features**
- Labels: `epic, phase-5, frontend`
- Milestone: `Phase 5: Visitor Features & Forms`

**Issue WP-54: Implement pre-chat form (name, email, department)**
- Labels: `phase-5, enhancement, frontend`
- Milestone: `Phase 5: Visitor Features & Forms`
- Acceptance: Configurable admin setting: enable/disable pre-chat form. Fields: Name (required/optional/hidden per setting), Email (required/optional/hidden), Department selector (if departments configured). If visitor is WP logged-in: auto-populate name + email, skip form. Form validation before chat starts. Submitted data stored in Firestore session visitorInfo.

**Issue WP-55: Implement offline message form (when no agents online)**
- Labels: `phase-5, enhancement, frontend, backend`
- Milestone: `Phase 5: Visitor Features & Forms`
- Acceptance: When all agents are offline or status = "away" and no active chats: widget shows offline form instead of chat. Fields: Name, Email, Message, Department (optional). Submitted via REST API (not direct Firestore write). PHP saves to `wp_adventchat_offline_messages` DB table AND sends notification email to all configured recipients. Admin setting: offline form on/off, recipient emails. Auto-reply email sent to visitor.

**Issue WP-56: Build offline messages admin list (WP_List_Table)**
- Labels: `phase-5, enhancement, backend, frontend`
- Milestone: `Phase 5: Visitor Features & Forms`
- Acceptance: "AdventChat → Offline Messages" admin page. WP_List_Table with columns: date, name, email, message (truncated), department, page, status (new/read). Bulk: mark read, delete. Click row: full message popover. Mark as read updates DB. Export to CSV. Badge on menu item showing unread count.

**Issue WP-57: Implement chat transcript email request**
- Labels: `phase-5, enhancement, frontend, backend`
- Milestone: `Phase 5: Visitor Features & Forms`
- Acceptance: Button/link in widget: "Email me this conversation". Input for email (pre-filled if in pre-chat form). On submit: REST API call → PHP compiles transcript as formatted HTML email → `wp_mail()`. Admin can also receive CC. Transcript includes: all messages, timestamps, visitor info, agent name. Configurable admin setting: enable/disable transcript feature.

**Issue WP-58: Implement chat rating / CSAT**
- Labels: `phase-5, enhancement, frontend, firebase`
- Milestone: `Phase 5: Visitor Features & Forms`
- Acceptance: After chat ends (agent clicks "End Chat"): widget shows 1-5 star rating prompt. Optional: text comment field. Rating stored in Firestore session `rating` field + `ratingComment`. Admin can view ratings in Chat Logs (Issue WP-59). Admin setting: enable/disable rating. Optional: show rating prompt only for chats > X minutes.

**Issue WP-59: Build chat logs admin list (WP_List_Table)**
- Labels: `phase-5, enhancement, backend, frontend`
- Milestone: `Phase 5: Visitor Features & Forms`
- Acceptance: "AdventChat → Chat Logs" admin page. WP_List_Table: date, visitor name/email, agent, duration, messages sent, rating, department. Filterable by date range, agent, department, rating. Click: modal or detail page showing full conversation (all messages). Export to CSV. Chat logs fetched from Firestore (via PHP → Firestore REST API) and optionally cached in WP DB.

**Issue WP-60: Implement file/image sharing (visitor and agent)**
- Labels: `phase-5, enhancement, frontend, firebase`
- Milestone: `Phase 5: Visitor Features & Forms`
- Acceptance: Both visitor and agent can attach files. Supported types: images (jpg, png, gif, webp), PDF, common office docs. Max size: 10MB. Files uploaded to Firebase Storage (same Firebase project). Upload progress indicator. Image previewed inline in chat. Non-image files show as download link with filename + size. Admin setting: enable/disable file sharing, max file size.

**Issue WP-61: GDPR consent checkbox (pre-chat form + widget)**
- Labels: `phase-5, enhancement, frontend, security`
- Milestone: `Phase 5: Visitor Features & Forms`
- Acceptance: Admin setting: enable GDPR consent checkbox on pre-chat form (required before starting chat), on offline form. Checkbox text customizable with link to privacy policy page. Consent stored in Firestore session. Admin setting: privacy policy page selector. Complies with GDPR Article 7. Cookie banner integration note in docs.

---

### PHASE 6: Appearance & Display Rules

**Issue WP-62: Epic — Appearance customization**
- Labels: `epic, phase-6, frontend`
- Milestone: `Phase 6: Appearance & Display Rules`

**Issue WP-63: Implement widget color theme customizer**
- Labels: `phase-6, enhancement, frontend`
- Milestone: `Phase 6: Appearance & Display Rules`
- Acceptance: WordPress color picker in Appearance settings tab. Settings: Primary color (widget header, send button, launcher), Secondary/accent color. Preview shown live in settings page via CSS custom properties. Color written as inline CSS vars on widget wrapper. Default: vivid blue (#0066FF) on white.

**Issue WP-64: Widget position and launcher style settings**
- Labels: `phase-6, enhancement, frontend`
- Milestone: `Phase 6: Appearance & Display Rules`
- Acceptance: Settings: Position (bottom-right, bottom-left). X offset (px), Y offset (px). Launcher style: Bubble (round icon), Tab (label on edge), or Custom Image (upload). Launcher icon: message bubble SVG default, customizable. Preview in settings. CSS computed dynamically.

**Issue WP-65: Widget display rules (show/hide per page/post type/user role)**
- Labels: `phase-6, enhancement, backend`
- Milestone: `Phase 6: Appearance & Display Rules`
- Acceptance: Admin setting: "Show on all pages" (default) OR custom rules. Rule builder: Include/Exclude specific pages, posts, post types, WooCommerce pages, categories. Separate toggle: hide on mobile devices. Separate toggle: hide from logged-out visitors. Separate toggle: hide from specific user roles. Evaluated in `wp_footer` hook via PHP before enqueuing widget.

**Issue WP-66: Widget auto-open with configurable delay**
- Labels: `phase-6, enhancement, frontend`
- Milestone: `Phase 6: Appearance & Display Rules`
- Acceptance: Admin setting (Appearance tab): Auto-open delay. Options: Disabled (default), 5s, 10s, 15s, 30s, 60s, Custom (seconds). Widget auto-opens after delay only if: visitor hasn't manually closed it this session (stored in sessionStorage). Agent is online. Auto-open animation is smooth.

**Issue WP-67: Widget welcome message and greeting customization**
- Labels: `phase-6, enhancement, frontend`
- Milestone: `Phase 6: Appearance & Display Rules`
- Acceptance: Admin settings for all widget-facing strings: greeting title, subtitle, placeholder text, send button text, offline form title, offline success message, pre-chat form title. Supports `{agent_name}`, `{site_name}` variables. Foundation for WPML/Polylang translation.

**Issue WP-68: Custom CSS field for widget styling**
- Labels: `phase-6, enhancement, frontend`
- Milestone: `Phase 6: Appearance & Display Rules`
- Acceptance: Admin Appearance tab: "Custom CSS" textarea. CSS scoped to `.adventchat-widget` wrapper. Injected as `<style>` in `wp_footer` only when widget loads. Basic CSS docs link. Sanitized (strip PHP, strip `<script>`).

---

### PHASE 7: Integrations

**Issue WP-69: Epic — Third-party integrations**
- Labels: `epic, phase-7`
- Milestone: `Phase 7: Integrations`

**Issue WP-70: WooCommerce integration — visitor cart context**
- Labels: `phase-7, enhancement, backend, woocommerce`
- Milestone: `Phase 7: Integrations`
- Acceptance: When WooCommerce active: visitor cart data (items, quantities, total, currency) included in session visitorInfo.wooContext via PHP (localized into widget JS). Console sidebar shows cart as item list with images. All hooks conditional on WooCommerce existence (`class_exists('WooCommerce')`).

**Issue WP-71: WooCommerce integration — current order context**
- Labels: `phase-7, enhancement, backend, woocommerce`
- Milestone: `Phase 7: Integrations`
- Acceptance: If visitor is on order-received page or my-account/orders: current/recent order ID included in visitorInfo. Console shows order details: items, total, status, billing. Agent can click to open WP Admin order page. Conditional on WooCommerce.

**Issue WP-72: WooCommerce integration — visitor identity from WC account**
- Labels: `phase-7, enhancement, backend, woocommerce`
- Milestone: `Phase 7: Integrations`
- Acceptance: If visitor is logged into WooCommerce: auto-populate pre-chat form name + email from WP user profile. Pass WC customer lifetime value and order count to console sidebar. Skip GDPR consent if user has accepted T&C (WC account creation). Conditional on WooCommerce.

**Issue WP-73: WPML & Polylang multilingual support**
- Labels: `phase-7, enhancement, backend, frontend`
- Milestone: `Phase 7: Integrations`
- Acceptance: Plugin text domain `adventchat` with full `.pot` file. WPML config XML (`wpml-config.xml`) mapping translatable option keys. Polylang string registration for all widget-visible strings (`pll_register_string`). Widget strings passed via JS config support translated values. Offline messages sent in site's current language.

**Issue WP-74: WordPress email notifications (offline messages, system alerts)**
- Labels: `phase-7, enhancement, backend`
- Milestone: `Phase 7: Integrations`
- Acceptance: `AdventChat_Mailer` class. Emails sent: new offline message (to configured recipients), new chat session started (optional, per-agent setting), agent not available warning. `AdventChat_Email_Template` HTML email template (responsive). Configurable: from name, from email. Uses `wp_mail()`. Admin can preview email templates.

**Issue WP-75: Elementor widget integration**
- Labels: `phase-7, enhancement, frontend`
- Milestone: `Phase 7: Integrations`
- Acceptance: If Elementor active: registers AdventChat widget in Elementor panel under "AdventChat" category. Widget adds the trigger button / launcher in a designated page section. Settings mirror widget appearance settings. Conditional on Elementor existence.

**Issue WP-76: REST API endpoint for widget config (SPA loading)**
- Labels: `phase-7, enhancement, backend, api`
- Milestone: `Phase 7: Integrations`
- Acceptance: `GET /wp-json/adventchat/v1/widget-config` — returns current widget configuration for current page (Firebase config, agent status, display rules result, siteId). Used by operator console SPA via AJAX. Cached for 60s. Rate-limited (10 req/min per IP). No authentication required (public endpoint, returns only non-sensitive data).

**Issue WP-77: Identity verification for logged-in visitors**
- Labels: `phase-7, enhancement, backend, security`
- Milestone: `Phase 7: Integrations`
- Acceptance: When visitor is logged-in WP user: generate `hash_hmac('sha256', (string)$user->ID, $secret)` as identity verification hash. Hash and user ID passed in widget JS config. Console displays "Verified" badge next to logged-in visitor names. Admin can set identity verification secret in settings (auto-generated on first save).

---

### PHASE 8: Premium Tier

**Issue WP-78: Epic — Premium tier and license system**
- Labels: `epic, phase-8`
- Milestone: `Phase 8: Premium Tier`

**Issue WP-79: Lemon Squeezy webhooks endpoint and license validation**
- Labels: `phase-8, enhancement, backend, api`
- Milestone: `Phase 8: Premium Tier`
- Acceptance: `AdventChat_License` class. `POST /wp-json/adventchat/v1/validate-license` endpoint sends license key to adventchat.com validation API. Response: `{ valid, plan, expires_at }`. License key + status cached in WP options (re-validate max once per 24h). Invalid/expired license: paid features gracefully disabled, admin notice shown.

**Issue WP-80: Premium feature gating (pro/free tier checks)**
- Labels: `phase-8, enhancement, backend`
- Milestone: `Phase 8: Premium Tier`
- Acceptance: `AdventChat_License::is_pro()` and `::is_agency()` checks. Free tier: all features available with own Firebase. Paid-only features: AdventChat-hosted Firebase (no config needed), access to mobile app. Feature gate wrapper: `<?php if ( AdventChat_License::is_pro() ) : ?>`. Upsell prompts in admin with features list, pricing table link.

**Issue WP-81: Hosted Firebase provisioning (paid tier)**
- Labels: `phase-8, enhancement, backend, firebase`
- Milestone: `Phase 8: Premium Tier`
- Acceptance: On license activation (paid tier): plugin calls adventchat.com provisioning API to get hosted Firebase Web App Config for this site's siteId. Config returned and stored encrypted. Site now uses AdventChat's shared Firestore project (partitioned by siteId). Provisioning API on adventchat.com managed separately. No local Firebase project setup needed for paid users.

**Issue WP-82: AdventChat admin account connection**
- Labels: `phase-8, enhancement, backend, api`
- Milestone: `Phase 8: Premium Tier`
- Acceptance: Settings: "Connect to AdventChat account" field (license key input). On save: calls validation API, retrieves plan, stores result. "Account" section in settings shows: plan name, expiry, manage subscription link (adventchat.com). Disconnect button. Admin notice if license expired (with renew link).

**Issue WP-83: Mobile app FCM helper (server-side push dispatch)**
- Labels: `phase-8, enhancement, backend, firebase, mobile`
- Milestone: `Phase 8: Premium Tier`
- Acceptance: `AdventChat_Push` PHP class. When new chat session created (visitor waiting): sends FCM push notification to all online operators' registered FCM tokens (stored in Firestore agents collection, PHP reads via Firestore REST API). Notification payload: visitor name, page, session ID (for deep link). Uses Firebase Cloud Messaging HTTP v1 API with service account (paid tier only, not needed for free). Handles: invalid token cleanup, batch sends.

**Issue WP-84: Usage analytics and admin dashboard widget**
- Labels: `phase-8, enhancement, backend, frontend`
- Milestone: `Phase 8: Premium Tier`
- Acceptance: WordPress Dashboard widget (admin_dashboard) showing: today's chats, avg rating, avg response time, online agents, total offline messages. Data fetched from Firestore (cached 5min). Sparkline chart for chats/day last 7 days. Free tier: basic stats. Pro tier: 30-day history, per-agent breakdown. Powered by Chart.js.

---

### PHASE 9: Testing & Launch

**Issue WP-85: Unit and integration tests setup (PHPUnit)**
- Labels: `phase-9, testing, backend`
- Milestone: `Phase 9: Launch Prep`
- Acceptance: PHPUnit configured with `phpunit.xml.dist`. WordPress test suite via WP-CLI scaffold. Test classes for: AdventChat_Settings, AdventChat_License, AdventChat_Mailer, AdventChat_Firebase_Admin, REST API endpoints. `composer.json` dev dependency for phpunit/phpunit. GitHub Actions CI runs tests on push.

**Issue WP-86: JavaScript widget unit tests (Jest)**
- Labels: `phase-9, testing, frontend`
- Milestone: `Phase 9: Launch Prep`
- Acceptance: Jest configured in package.json. Tests for: widget initialization, pre-chat form validation, typing debounce, message rendering, offline mode detection. Coverage > 70%.

**Issue WP-87: Security audit and hardening**
- Labels: `phase-9, security, backend`
- Milestone: `Phase 9: Launch Prep`
- Acceptance: All user inputs sanitized (`sanitize_text_field`, `wp_kses`, etc.). All outputs escaped (`esc_html`, `esc_attr`, `esc_url`). All AJAX/REST endpoints have nonce/capability checks. SQL queries use `$wpdb->prepare()`. Firebase config encrypted at rest. No direct file access (all files start with `defined('ABSPATH') || exit;`). `headers` include `X-Content-Type-Options: nosniff`. OWASP Top 10 review completed.

**Issue WP-88: Performance optimization and asset loading**
- Labels: `phase-9, performance, frontend`
- Milestone: `Phase 9: Launch Prep`
- Acceptance: Widget JS < 30KB gzipped. Firebase SDK loaded from Google CDN with `crossorigin` attribute. Assets minified and versioned (cache busting). Widget loaded asynchronously (`defer`). No render-blocking resources. Admin assets only loaded on AdventChat admin pages. `wp_is_mobile()` check before loading on mobile if hidden-on-mobile is set.

**Issue WP-89: WordPress.org plugin submission preparation**
- Labels: `phase-9, documentation`
- Milestone: `Phase 9: Launch Prep`
- Acceptance: `readme.txt` in WordPress.org format (Description, Installation, FAQ, Screenshots, Changelog). Screenshots: widget, operator console, settings page, mobile app (6 screenshots). Plugin header complete (License, License URI, Requires at least, Tested up to, Requires PHP). No GPL-incompatible code. No base64-encoded PHP. `assets/` directory for banner (772x250) and icon (128x128, 256x256). SVN commit ready.

**Issue WP-90: Final testing on WordPress.org recommended environments**
- Labels: `phase-9, testing`
- Milestone: `Phase 9: Launch Prep`
- Acceptance: Tested on: WordPress 6.5+, PHP 8.1, 8.2, 8.3. Tested with: WooCommerce, WPML, Polylang, Elementor, GeneratePress, OceanWP, default Twenty-Twenty-Five theme. No PHP notices or warnings. Plugin Check (PCP) tool passes. Tested in multisite (deactivates gracefully if not supported).

---

## 📝 STEP 7 — Create All Issues: Mobile App (`maxymurm/adventchat-mobile`)

---

### PHASE 0: Mobile Project Setup

**Issue MB-1: Initialize Git and create GitHub repository (private)**
- Labels: `setup, phase-0`
- Milestone: `Phase 0: Project Setup`
- Acceptance: git init, gh repo create private, main + develop branches, pushed

**Issue MB-2: Create project board, labels, milestones**
- Labels: `setup, phase-0`
- Milestone: `Phase 0: Project Setup`
- Acceptance: GitHub Projects v2 board created, all labels, all milestones

**Issue MB-3: Scaffold Ionic 8 + Angular 18 project**
- Labels: `setup, phase-1, enhancement`
- Milestone: `Phase 1: Foundation`
- Acceptance: `ionic start adventchat-mobile tabs --type=angular --capacitor`. App name `AdventChat`. Package ID `com.adventchat.mobile`. Ionic 8, Angular 18 standalone components. TypeScript strict mode. Commits Ionic scaffold.

**Issue MB-4: Configure Capacitor (iOS + Android targets)**
- Labels: `setup, phase-1, enhancement, mobile`
- Milestone: `Phase 1: Foundation`
- Acceptance: Capacitor 6 configured. `capacitor.config.ts` with appId `com.adventchat.mobile`, appName `AdventChat`. Run: `npx cap add ios` and `npx cap add android`. Verify: `npx cap sync` runs without errors. Both `ios/` and `android/` gitignored but documented.

---

### PHASE 1: Foundation

**Issue MB-5: Install and configure Firebase SDK for Angular**
- Labels: `phase-1, enhancement, firebase`
- Milestone: `Phase 1: Foundation`
- Acceptance: `npm install firebase @angular/fire`. `AngularFireModule.initializeApp(environment.firebaseConfig)` in app config. `environment.ts` template with placeholder (gitignored when using real credentials). Firestore + Auth modules imported. Tested: Firestore reads work in dev.

**Issue MB-6: Implement base tab navigation (Queue | Chats | History | Settings)**
- Labels: `phase-1, enhancement, frontend`
- Milestone: `Phase 1: Foundation`
- Acceptance: 4 bottom tabs: Queue (bell icon), Chats (message icon), History (clock icon), Settings (gear icon). Badge on Queue tab showing waiting visitor count. Angular standalone components. Lazy-loaded route modules per tab. Tab names and icons from Ionic IonIcon library.

**Issue MB-7: Implement global theme (colors, typography, dark mode)**
- Labels: `phase-1, enhancement, frontend`
- Milestone: `Phase 1: Foundation`
- Acceptance: `src/theme/variables.scss` with `--ion-color-primary: #0066FF`. Full light/dark mode: `prefers-color-scheme: dark` media query + manual toggle. Custom AdventChat color palette. Consistent typography with Ionic defaults. Dark mode toggle in Settings tab.

**Issue MB-8: Create all core data models (TypeScript interfaces)**
- Labels: `phase-1, enhancement`
- Milestone: `Phase 1: Foundation`
- Acceptance: `src/app/core/models/`: session.model.ts, message.model.ts, agent.model.ts, visitor.model.ts, department.model.ts, macro.model.ts. All fields match Firestore schema from WP plugin memory file. Strict TypeScript types.

**Issue MB-9: Create Firebase service (Firestore connection + helpers)**
- Labels: `phase-1, enhancement, firebase`
- Milestone: `Phase 1: Foundation`
- Acceptance: `FirebaseService` injectable class. Methods: `connectToSite(siteId, firebaseConfig)` — initializes Firestore with correct project. `getSessionsQuery(agentId)`, `getMessagesQuery(sessionId)`. Handles multiple Firebase projects (paid tier = AdventChat's Firestore, free tier = user's Firestore). Uses Angular Signals for reactive state.

**Issue MB-10: Create auth service (AdventChat account + Firebase token exchange)**
- Labels: `phase-1, enhancement, api`
- Milestone: `Phase 1: Foundation`
- Acceptance: `AuthService`. `login(email, password)`: POST to `https://adventchat.com/wp-json/adventchat/v1/validate-license` → receives `{ token, firebase_config, site_id, plan }`. Stores JWT in Capacitor Preferences (Keychain/Keystore). `signInToFirebase(firebaseConfig, agentId)`: signs into Firestore project. `logout()`: clears all stored credentials. `isAuthenticated()` signal.

**Issue MB-11: Create JWT interceptor**
- Labels: `phase-1, enhancement, api`
- Milestone: `Phase 1: Foundation`
- Acceptance: `JwtInterceptor` attaches `Authorization: Bearer <token>` to all HTTP requests to adventchat.com domain. Reads token from Capacitor Preferences. Token expiry check: if expired, shows login screen (no silent refresh needed — re-login flow).

---

### PHASE 2: Chat Interface

**Issue MB-12: Epic — WhatsApp-style chat interface**
- Labels: `epic, phase-2, frontend`
- Milestone: `Phase 2: Chat Interface`

**Issue MB-13: Visitor queue page (real-time waiting list)**
- Labels: `phase-2, enhancement, frontend, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: Queue tab shows all `status: "waiting"` sessions for agent's site. Real-time via Firestore `onSnapshot`. Cards: visitor name/emoji avatar, current page (domain only), wait time (live countdown), department badge. Sorted: longest wait first. Pull-to-refresh. Empty state animation. Card swipe-left: "Accept" action (native iOS/Android haptic).

**Issue MB-14: Accept chat action (assign session to self)**
- Labels: `phase-2, enhancement, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: Tapping "Accept" updates Firestore: session `status: "active"`, `assignedAgentId: currentAgentId`. Session removed from queue, added to Chats tab. Haptic feedback (Capacitor Haptics). Animated transition to chat window.

**Issue MB-15: WhatsApp-style chat window**
- Labels: `phase-2, enhancement, frontend, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: `ChatWindowPage`. Messages loaded from Firestore subcollection. Real-time `onSnapshot` listener. Message bubbles: agent = right, blue background. Visitor = left, gray background. Timestamps below each bubble group. Visitor avatar/initials. Message input bar: IonFooter with text input, send button, attachment button, macro button. "End Chat" button in header. Auto-scroll to bottom on new message.

**Issue MB-16: Real-time message sending (agent side)**
- Labels: `phase-2, enhancement, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: Text message: writes to Firestore messages subcollection. `senderType: "agent"`, `senderId: agentFirebaseUID`, `sentAt: serverTimestamp()`. Optimistic UI. Input cleared after send. Max 2000 chars. Keyboard pushes content up (Ionic IonContent with keyboard padding).

**Issue MB-17: Typing indicators (real-time)**
- Labels: `phase-2, enhancement, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: Writes to `/sites/{siteId}/typing/{sessionId}/agentTyping` with debounce 3s. Reads visitorTyping from same document. Shows "Visitor is typing..." with animated 3-dot indicator at bottom of messages. Cleared when message sent.

**Issue MB-18: Read receipts (mark messages as read)**
- Labels: `phase-2, enhancement, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: When chat window is in foreground and visible: marks all unread visitor messages `readAt: serverTimestamp()`. Blue double-tick for messages read by agent. Widget reads this same field.

**Issue MB-19: File and image sharing (agent sends)**
- Labels: `phase-2, enhancement, mobile, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: Attachment button opens: Photo Library, Camera, File. Uses `@capacitor/camera` for photos. Image uploaded to Firebase Storage. Progress bar shown. Image rendered inline in chat bubble. Documents shown as download card with icon + filename. Max 10MB.

**Issue MB-20: Visitor context panel (bottom sheet)**
- Labels: `phase-2, enhancement, frontend, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: Info button in chat header opens IonModal (bottom sheet). Shows: visitor name + email, current page URL, all pages visited in session, browser/OS icons, country flag + IP, WooCommerce cart (if available), session duration, previous sessions count. Department selector. Transfer button.

**Issue MB-21: Multi-chat management (Chats tab)**
- Labels: `phase-2, enhancement, frontend, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: Chats tab: list of all agent's `status: "active"` sessions. Real-time updates. Unread count badge. Last message preview. Tap: opens chat window. Swipe to end chat. Sorted: most recent message first.

**Issue MB-22: Canned responses (macros) picker in chat**
- Labels: `phase-2, enhancement, frontend, firebase`
- Milestone: `Phase 2: Chat Interface`
- Acceptance: Macro button (speech bubble icon) in input bar. Opens searchable list (IonSearchbar + IonList). Macros loaded from Firestore in real time. Search filters by title. Tap macro: inserts text into input (editable before sending). Includes shared + agent-specific macros.

---

### PHASE 3: Push Notifications

**Issue MB-23: Epic — Firebase push notifications**
- Labels: `epic, phase-3, firebase, mobile`
- Milestone: `Phase 3: Push Notifications`

**Issue MB-24: Configure FCM for iOS (APNs certificate)**
- Labels: `phase-3, enhancement, mobile, firebase`
- Milestone: `Phase 3: Push Notifications`
- Acceptance: APNs Auth Key (.p8) uploaded to Firebase Console. `GoogleService-Info.plist` added to Xcode project. `@capacitor-community/firebase-analytics` or `@capacitor/push-notifications` configured. iOS entitlements: Push Notifications enabled. Tested: FCM token generated on physical iOS device.

**Issue MB-25: Configure FCM for Android (google-services.json)**
- Labels: `phase-3, enhancement, mobile, firebase`
- Milestone: `Phase 3: Push Notifications`
- Acceptance: `google-services.json` added to Android project. FCM configured in Firebase Console. Notification icon (white, transparent background) added to Android assets. FCM token generated on physical Android device. Tested: notification received.

**Issue MB-26: Register FCM token on login and update in Firestore**
- Labels: `phase-3, enhancement, firebase`
- Milestone: `Phase 3: Push Notifications`
- Acceptance: On app launch after login: request push permission (iOS permission prompt). Get FCM token. Write/update to Firestore `/sites/{siteId}/agents/{agentId}/fcmTokens` (array of tokens, handles multiple installs per agent). Token refreshed via `onTokenRefresh`. Old/invalid tokens cleaned up by WP plugin PHP when FCM returns INVALID_REGISTRATION error.

**Issue MB-27: Handle foreground push notifications (in-app notification)**
- Labels: `phase-3, enhancement, frontend, mobile`
- Milestone: `Phase 3: Push Notifications`
- Acceptance: When app is open + notification arrives: show IonToast or custom in-app banner at top (not native push — app already open). Sound (Capacitor Haptics + IonSound). Banner shows: visitor name, page, preview. Tap: navigates to that chat session. Notification stays 5s then slides away.

**Issue MB-28: Handle background/killed push notifications (deep link)**
- Labels: `phase-3, enhancement, mobile, firebase`
- Milestone: `Phase 3: Push Notifications`
- Acceptance: When app is backgrounded or killed: FCM delivers native push notification. Notification payload includes `sessionId`. Tapping notification: opens app or brings to foreground and navigates directly to the correct chat. Uses Capacitor Push Notifications `pushNotificationActionPerformed` event. Works on both iOS and Android.

**Issue MB-29: Notification permission request flow (iOS)**
- Labels: `phase-3, enhancement, mobile`
- Milestone: `Phase 3: Push Notifications`
- Acceptance: Don't request permission on first app launch. Show custom pre-permission screen: "Enable notifications so you never miss a visitor chat" with "Enable" and "Not now" buttons. Tapping "Enable": shows native iOS permission prompt. "Not now": defers (can request from Settings later). iOS push permission logic with `Capacitor.Plugins.PushNotifications.requestPermissions()`.

**Issue MB-30: Notification badge count management**
- Labels: `phase-3, enhancement, mobile`
- Milestone: `Phase 3: Push Notifications`
- Acceptance: App badge count = total waiting sessions + unread messages across all active chats. Updated from Firestore snapshot. When all chats read: badge cleared. Uses `@capacitor/app-launcher` or Badge plugin. Works correctly when app is killed (FCM notification includes badge count in payload). Android: notification dot via NotificationChannel.

---

### PHASE 4: Operator Features

**Issue MB-31: Epic — Agent productivity features**
- Labels: `epic, phase-4`
- Milestone: `Phase 4: Operator Features`

**Issue MB-32: Agent presence status toggle (online/away/offline)**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Features`
- Acceptance: Persistent status bar / FAB in app. Three states: Online (green), Away (yellow), Offline (red). Status written to Firestore `/agents/{agentId}/status`. App goes "away" automatically when backgrounded for > 5 minutes. Goes "offline" when force-closed or logged out. Visitor widget checks agent status in real-time.

**Issue MB-33: Chat transfer (reassign to agent or department)**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Features`
- Acceptance: Transfer button in chat header. Opens modal: list of online agents (with active chat count) + departments. Select recipient: updates Firestore `assignedAgentId`. Recipient receives push notification. System message appended to chat: "Chat transferred to [Agent Name]". Visitor sees: "You've been connected to [Agent Name]".

**Issue MB-34: Internal notes in chat**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Features`
- Acceptance: "Note" button in chat actions toolbar. Note written to messages subcollection with `type: "internal"`. Rendered with yellow background, "INTERNAL NOTE" label. Not visible to visitor widget (widget filters `type != "internal"`). Notes visible to ALL agents who view the chat.

**Issue MB-35: Departments view and filtering**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Features`
- Acceptance: Queue tab has department filter chips (horizontal scroll). "All" chip selected by default. Each department chip shows waiting count badge. Selecting a department chip filters queue to that department's sessions only. Agent's department(s) highlighted. Settings page shows which departments agent belongs to.

**Issue MB-36: Chat history / logs (History tab)**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Features`
- Acceptance: History tab: list of `status: "ended"` sessions assigned to current agent (last 30 days). Paginated (20 per page). Shows: visitor name, date, duration, rating stars. Search by visitor name/email. Tap: view full conversation (read-only, with notes). Load more button.

**Issue MB-37: Agent settings page**
- Labels: `phase-4, enhancement, frontend`
- Milestone: `Phase 4: Operator Features`
- Acceptance: Settings tab contains: Profile (avatar, display name), Notification preferences (per-type toggle), Sound on/off, Dark mode toggle, Active site (multi-site selector), Subscription status (plan name, "Manage at adventchat.com" link), Sign out. Changes to display name synced to Firestore agent document.

**Issue MB-38: Multi-site support (switch between connected WP sites)**
- Labels: `phase-4, enhancement, frontend, firebase`
- Milestone: `Phase 4: Operator Features`
- Acceptance: Settings → "Sites" shows all linked WordPress sites. Each site: name, domain, plan badge, last activity, online agents count. Tap to switch active site (app re-initializes Firestore connection). Add site: enter login credentials for that WordPress site. Sites list stored in Capacitor Preferences.

---

### PHASE 5: Auth & Account

**Issue MB-39: Login screen (AdventChat account)**
- Labels: `phase-5, enhancement, frontend`
- Milestone: `Phase 5: Auth & Account`
- Acceptance: Clean login screen with AdventChat logo (placeholder). Email + password fields. "Sign in" button. "Subscribe at adventchat.com" link for new users. "Forgot password?" link (opens adventchat.com in in-app browser). Error messages: wrong credentials, network error, no subscription. Loading state on submit.

**Issue MB-40: Apple Sign-In for operator login**
- Labels: `phase-5, enhancement, mobile`
- Milestone: `Phase 5: Auth & Account`
- Acceptance: `@capacitor-community/apple-sign-in` plugin. "Sign in with Apple" button (required on iOS if any social login present). Sends `identityToken` to adventchat.com for validation. Backend validates token + checks subscription. Returns app JWT + Firebase config. Apple only provides email on first login — cache in Capacitor Preferences. Handles Apple's email relay service.

**Issue MB-41: Google Sign-In for operator login**
- Labels: `phase-5, enhancement, mobile`
- Milestone: `Phase 5: Auth & Account`
- Acceptance: `@codetrix-studio/capacitor-google-auth` plugin. "Sign in with Google" button. Separate Client IDs for iOS and Android (Firebase console). Sends `accessToken` to adventchat.com for validation. Backend validates + checks subscription. Returns app JWT + Firebase config.

**Issue MB-42: Token storage and session persistence**
- Labels: `phase-5, enhancement, mobile`
- Milestone: `Phase 5: Auth & Account`
- Acceptance: JWT stored in Capacitor Preferences (iOS Keychain, Android Keystore). On app launch: reads token, validates locally (expiry check). If valid: skip login, re-authenticate Firebase. If expired (60-day token): show login screen. Biometric authentication option (FaceID/fingerprint) to unlock app without full re-login. Use `@capacitor-community/biometric-auth`.

**Issue MB-43: Sign out and account management**
- Labels: `phase-5, enhancement, mobile`
- Milestone: `Phase 5: Auth & Account`
- Acceptance: Sign out: clears JWT, Firebase auth state, FCM token removed from Firestore. Confirmation dialog: "You'll miss new chats if you sign out while operators are online." FCM token deregistered. App navigates to login screen. "Delete account" option in settings (opens adventchat.com account page in browser).

---

### PHASE 6: Polish & App Stores

**Issue MB-44: App icon and splash screen design**
- Labels: `phase-6, enhancement, mobile`
- Milestone: `Phase 6: Polish & App Stores`
- Acceptance: App icon: AdventChat branded (placeholder, user will replace). All required sizes generated for iOS (1024x1024 max) and Android (adaptive icon). Splash screen: brand color background + logo, matches dark mode. Use `@capacitor/splash-screen`. Automated icon generation with `cordova-res`.

**Issue MB-45: Deep links for push notification navigation**
- Labels: `phase-6, enhancement, mobile`
- Milestone: `Phase 6: Polish & App Stores`
- Acceptance: Universal links (iOS) and App Links (Android) configured. Deep link scheme `adventchat://chat/{sessionId}`. Tapping push notification opens app directly to correct chat. Angular Router handles deep link URL parsing. Tested on both platforms.

**Issue MB-46: Firebase Crashlytics integration**
- Labels: `phase-6, enhancement, mobile, firebase`
- Milestone: `Phase 6: Polish & App Stores`
- Acceptance: `@capacitor-community/firebase-analytics` (includes Crashlytics). Crashes reported to Firebase Console. Custom log events: `chat_accepted`, `chat_ended`, `login_success`, `login_failed`. User identifier set to agentId (not PII) for crash grouping. Non-fatal errors reported with context.

**Issue MB-47: Production build with code signing and environment config**
- Labels: `phase-6, enhancement, mobile`
- Milestone: `Phase 6: Polish & App Stores`
- Acceptance: `environment.prod.ts` with production Firebase config + API URL. `ng build --configuration=production` runs without errors. iOS: code signing configured in Xcode (Team ID, bundle identifier, provisioning profile). Android: `release` keystore generated + `build.gradle` signing config. Both apps build to `.ipa` / `.apk` / `.aab` without errors.

**Issue MB-48: iOS App Store submission**
- Labels: `phase-6, enhancement, mobile`
- Milestone: `Phase 6: Polish & App Stores`
- Acceptance: App Store Connect: app created, categories (Business, Utilities), description, keywords, screenshots (6.7", 6.1" required). Privacy policy URL: adventchat.com/privacy. TestFlight internal testing: at least 2 testers invited. App Review information: demo account provided. App submitted for review. All App Store review guidelines checklist items verified.

**Issue MB-49: Google Play Store submission**
- Labels: `phase-6, enhancement, mobile`
- Milestone: `Phase 6: Polish & App Stores`
- Acceptance: Google Play Console: app created, content rating completed, data safety form completed. `.aab` uploaded to Internal Testing track. Screenshots (phone + tablet optional). Description, short description, promo graphic. Internal testing: at least 2 testers. Promoted to Production track when approved.

**Issue MB-50: End-to-end testing on real devices**
- Labels: `phase-6, testing, mobile`
- Milestone: `Phase 6: Polish & App Stores`
- Acceptance: Test on physical iOS device (iPhone 15 or similar). Test on physical Android device (Pixel or Samsung). Test scenarios: login, receive chat notification, accept chat, full conversation, end chat, file sharing, typing indicators, offline mode. All scenarios pass. No crashes detected.

---

## 🔄 STEP 8 — Add All Issues to Project Boards

After creating all issues, collect issue IDs and add them to the respective GitHub Projects v2 boards using:

```bash
# Get project ID first (replace with actual board ID from STEP 5)
PROJECT_ID="PVT_xxx"   # WP plugin board
MOBILE_PROJECT_ID="PVT_yyy"  # Mobile app board

# Add each WP plugin issue to WP plugin board
for i in $(gh issue list --repo maxymurm/adventchat --limit 100 --json number --jq '.[].number'); do
  ISSUE_ID=$(gh api repos/maxymurm/adventchat/issues/$i --jq '.node_id')
  gh api graphql -f query="mutation { addProjectV2ItemById(input: { projectId: \"$PROJECT_ID\" contentId: \"$ISSUE_ID\" }) { item { id } } }"
done

# Add each mobile issue to mobile board (same pattern with maxymurm/adventchat-mobile)
```

---

## 📁 STEP 9 — Create Planning Documents

Create `docs/planning/full-scope.md` in both project folders with:
- Complete feature inventory organized by phase
- Technical decisions and rationale (from memory.instruction.md)
- Firestore data schema (from memory.instruction.md)
- Timeline estimate per phase
- Dependency map between issues

---

## 📌 STEP 10 — Update Memory Files

After completing all steps, update `.github/instructions/memory.instruction.md` in BOTH projects:

**WP plugin memory updates:**
- Active Phase: Phase 0: Project Setup → Phase 1: Plugin Foundation
- Current Branch: develop
- Last Activity: [timestamp] GitHub repos created, project boards configured, N issues created
- Next Steps: Begin Phase 1 development (Issue #WP-9 first)

**Mobile app memory updates:**
- Active Phase: Phase 0: Project Setup → Phase 1: Foundation
- Current Branch: develop
- Last Activity: [timestamp] GitHub repo created, N issues created

---

## 💾 STEP 11 — Commit Everything and Report

```powershell
# WP plugin
Set-Location "C:\Users\maxmm\Local Sites\advent-digest\app\public\wp-content\plugins\adventchat"
git add .
git commit -m "chore(setup): complete Phase 0 initialization - repos, boards, labels, milestones, issues created"
git push origin main
git push origin develop

# Mobile app
Set-Location "C:\Users\maxmm\projects\adventchat-mobile"
git add .
git commit -m "chore(setup): complete Phase 0 initialization - repo, board, labels, milestones, issues created"
git push origin main
git push origin develop
```

---

## 📊 STEP 12 — Final Report

When complete, provide:
1. ✅ GitHub repo URLs for both projects
2. ✅ Project board URLs for both projects
3. ✅ Total issues created (WP plugin: expected ~90, mobile: expected ~50)
4. ✅ Any blockers encountered (with details)
5. ✅ What the next development step is (should be: Phase 1 WP plugin, Issue #WP-9)
6. ✅ Updated ecosystem.md paths recorded

---

## 🗓️ RECOMMENDED EXECUTION ORDER (after issues created)

### WP Plugin — Development Phase Order
1. Phase 0 setup issues → Phase 1 foundation → Phase 2 Firebase → Phase 3 chat engine
2. Phase 4 operator console runs parallel to Phase 5 visitor features (independent)
3. Phase 6 appearance after core chat works
4. Phase 7 integrations after Phase 4+5 complete
5. Phase 8 premium tier last (gating requires everything else working)
6. Phase 9 testing/launch after Phase 8

### Mobile App — Start after WP Plugin Phase 3 complete
The mobile app needs the Firebase schema (Phase 2 WP) and chat engine (Phase 3 WP) working before the mobile chat interface is testable end-to-end.

### Suggested 8-week timeline:
- Weeks 1-2: WP Plugin Phase 0-2 (foundation + Firebase)
- Weeks 3-4: WP Plugin Phase 3-4 (chat engine + operator console)
- Week 5: WP Plugin Phase 5-6 (visitor features + appearance) + Mobile Phase 0-1
- Week 6: WP Plugin Phase 7-8 (integrations + premium) + Mobile Phase 2 (chat)
- Week 7: Mobile Phase 3-4 (push + features) + WP Plugin Phase 9 (testing)
- Week 8: Mobile Phase 5-6 (auth, polish, store submission) + WP Plugin launch
