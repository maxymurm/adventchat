# AdventChat WordPress Plugin — Full Scope

> **Status:** Phase 0 complete. Ready to begin Phase 1.
> **Last Updated:** Phase 0 initialization complete — all 90 issues created, boards populated.

---

## Overview

AdventChat is a freemium WordPress live chat plugin powered by Firebase/Firestore. It enables real-time chat between website visitors and operators, with a React+TypeScript operator console in WP Admin and a companion Ionic/Angular mobile app.

### Pricing Tiers

| Tier | Price | Key Benefit |
|------|-------|-------------|
| Free | $0/mo | Bring-your-own Firebase, all features |
| Pro | $24/mo | AdventChat hosted Firebase + mobile app |
| Agency | $59/mo | Unlimited workspaces + white-label |

---

## Technical Stack

- **Backend:** PHP 8.1+, WordPress 6.0+, GPL v3
- **Operator Console:** React 18 + TypeScript SPA in WP Admin
- **Visitor Widget:** Vanilla JS (no framework), < 30KB gzip
- **Real-time DB:** Cloud Firestore (NOT Realtime Database)
- **Visitor Auth:** Firebase Anonymous Authentication
- **Agent Auth:** Firebase Email/Password (auto-provisioned from WP users)
- **Billing:** Lemon Squeezy
- **Build:** esbuild / Vite

---

## Firestore Schema

```
/sites/{siteId}/
  sessions/{sessionId}/
    messages/{messageId}
  agents/{agentId}
  departments/{deptId}
  macros/{macroId}
  typing/{sessionId}
  offlineMessages/{id}
```

---

## Phase Roadmap

### Phase 0: Project Setup (COMPLETE)
- [x] Git repos initialized (adventchat + adventchat-mobile)
- [x] GitHub repos created (public/private)
- [x] Labels, milestones, project boards created
- [x] 90 WP issues + 50 mobile issues filed
- [x] Reference plugins analyzed

**Issues:** WP-1 through WP-8
**Start Next:** WP-9 (Plugin core architecture epic)

---

### Phase 1: Plugin Foundation
**Goal:** A working WordPress plugin that loads, activates, and provides the settings framework.

**Issues:** WP-9 through WP-19

| # | Title | Priority |
|---|-------|----------|
| WP-9 | EPIC - Plugin core architecture | high |
| WP-10 | Create main plugin file (adventchat.php) | high |
| WP-11 | Create main AdventChat bootstrap class | high |
| WP-12 | Plugin activation handler (DB tables) | high |
| WP-13 | Plugin deactivation and uninstall | normal |
| WP-14 | Plugin settings framework (tabbed admin) | high |
| WP-15 | AdventChat_Options helper class | normal |
| WP-16 | Custom WordPress operator role | normal |
| WP-17 | Plugin autoloader (PSR-4) | high |
| WP-18 | REST API base class | high |
| WP-19 | Build system setup (npm + esbuild) | high |

**Exit Criteria:** Plugin installs, activates, shows admin menu, settings save correctly.

---

### Phase 2: Firebase & Firestore
**Goal:** Firebase credentials configured and all auth flows working.

**Issues:** WP-20 through WP-28

| # | Title | Priority |
|---|-------|----------|
| WP-20 | EPIC - Firebase integration | high |
| WP-21 | Firestore data schema documentation | high |
| WP-22 | Firebase settings tab (Web App Config) | high |
| WP-23 | Firebase config validation + test connection | high |
| WP-24 | Firebase JavaScript SDK integration | high |
| WP-25 | Firebase Anonymous Auth for visitors | high |
| WP-26 | Firebase Email/Password auth for operators | high |
| WP-27 | Firestore Security Rules + admin UI | high |
| WP-28 | AdventChat_Firebase_Admin PHP class | normal |

**Exit Criteria:** Visitor can sign in anonymously; operator can sign in with credentials; security rules deployed.

---

### Phase 3: Chat Engine
**Goal:** Real-time chat works end-to-end between visitor widget and operator console.

**Issues:** WP-29 through WP-38

| # | Title | Priority |
|---|-------|----------|
| WP-29 | EPIC - Real-time chat engine | high |
| WP-30 | Visitor chat widget HTML/CSS shell | high |
| WP-31 | Widget injection into WP frontend | high |
| WP-32 | Chat session creation in Firestore | high |
| WP-33 | Real-time message sending (visitor) | high |
| WP-34 | Real-time message receiving (visitor) | high |
| WP-35 | Typing indicators | normal |
| WP-36 | Read receipts | normal |
| WP-37 | PHP visitor info collector | normal |
| WP-38 | Widget sound notifications | low |

**Exit Criteria:** Visitor sends message, operator sees it in real time, and vice versa.

---

### Phase 4: Operator Console
**Goal:** React SPA operator console is fully functional for managing and responding to chats.

**Issues:** WP-39 through WP-52

| # | Title | Priority |
|---|-------|----------|
| WP-39 | EPIC - Operator console SPA | high |
| WP-40 | Scaffold React + TypeScript console SPA | high |
| WP-41 | Console - Firebase operator sign-in | high |
| WP-42 | Console - Visitor queue panel | high |
| WP-43 | Console - Active chat window | high |
| WP-44 | Console - Visitor context sidebar | normal |
| WP-45 | Console - Multiple concurrent chats | high |
| WP-46 | Console - Canned responses (macros) | normal |
| WP-47 | Console - Agent status toggle | high |
| WP-48 | Console - Chat routing (round-robin) | normal |
| WP-49 | Console - Departments and routing | normal |
| WP-50 | Console - Chat transfer | normal |
| WP-51 | Console - Internal notes | normal |
| WP-52 | Admin - Macros management page | normal |

**Exit Criteria:** Operator can accept, chat, transfer, and close chats in the console.

---

### Phase 5: Visitor Features & Forms
**Goal:** Pre-chat form, offline messages, transcripts, ratings, file sharing, and GDPR.

**Issues:** WP-53 through WP-61

| # | Title | Priority |
|---|-------|----------|
| WP-53 | EPIC - Visitor-facing features | high |
| WP-54 | Pre-chat form (name, email, dept) | high |
| WP-55 | Offline message form | high |
| WP-56 | Offline messages admin list | normal |
| WP-57 | Chat transcript email request | normal |
| WP-58 | Chat rating / CSAT | normal |
| WP-59 | Chat logs admin list | normal |
| WP-60 | File/image sharing | normal |
| WP-61 | GDPR consent checkbox | high |

**Exit Criteria:** Visitors fill in pre-chat form; offline messages received by email; GDPR checkbox works.

---

### Phase 6: Appearance & Display Rules
**Goal:** Admins can fully customize the widget appearance and control where it shows.

**Issues:** WP-62 through WP-68

| # | Title | Priority |
|---|-------|----------|
| WP-62 | EPIC - Appearance customization | normal |
| WP-63 | Widget color theme customizer | normal |
| WP-64 | Widget position and launcher style | normal |
| WP-65 | Widget display rules | normal |
| WP-66 | Widget auto-open with delay | normal |
| WP-67 | Welcome message customization | normal |
| WP-68 | Custom CSS field | low |

**Exit Criteria:** Widget matches site brand; display rules filter correctly on actual pages.

---

### Phase 7: Integrations
**Goal:** WooCommerce, WPML, Polylang, Elementor, and email notification integrations.

**Issues:** WP-69 through WP-77

| # | Title | Priority |
|---|-------|----------|
| WP-69 | EPIC - Third-party integrations | normal |
| WP-70 | WooCommerce - visitor cart context | normal |
| WP-71 | WooCommerce - current order context | normal |
| WP-72 | WooCommerce - visitor identity | normal |
| WP-73 | WPML and Polylang support | normal |
| WP-74 | WordPress email notifications | high |
| WP-75 | Elementor widget integration | low |
| WP-76 | REST API widget config endpoint | high |
| WP-77 | Identity verification (logged-in visitors) | high |

**Exit Criteria:** WooCommerce cart shown in console; WPML strings registerable.

---

### Phase 8: Premium Tier
**Goal:** License validation, feature gating, hosted Firebase provisioning, and mobile push dispatch.

**Issues:** WP-78 through WP-84

| # | Title | Priority |
|---|-------|----------|
| WP-78 | EPIC - Premium tier and license system | high |
| WP-79 | Lemon Squeezy license validation | high |
| WP-80 | Premium feature gating | high |
| WP-81 | Hosted Firebase provisioning | high |
| WP-82 | AdventChat admin account connection | high |
| WP-83 | Mobile app FCM push dispatch | normal |
| WP-84 | Usage analytics dashboard widget | normal |

**Exit Criteria:** License key validates; Pro features unlock; hosted Firebase config provisioned.

---

### Phase 9: Launch Prep
**Goal:** Tests, security audit, performance optimization, WordPress.org submission prep.

**Issues:** WP-85 through WP-90

| # | Title | Priority |
|---|-------|----------|
| WP-85 | Unit + integration tests (PHPUnit) | high |
| WP-86 | JavaScript widget unit tests (Jest) | high |
| WP-87 | Security audit and hardening | critical |
| WP-88 | Performance optimization | high |
| WP-89 | WordPress.org submission prep | high |
| WP-90 | Final cross-env testing | high |

**Exit Criteria:** Plugin Check (PCP) passes; PHPUnit + Jest green; Widget < 30KB gzip.

---

## First Task

**Start with:** [WP-9: EPIC - Plugin core architecture and bootstrap](https://github.com/maxymurm/adventchat/issues/9)

Then proceed with WP-10 (main plugin file) — this is the first real code file.

---

## Key Architectural Decisions (Non-Negotiable)

1. **Firestore only** — no Realtime Database
2. **Visitors = Firebase Anonymous Auth** — no user-visible login ever
3. **Operators = Firebase Email/Password** — auto-provisioned when WP capability granted
4. **No service account** — Firebase Web App Config (6 fields) only, stored encrypted
5. **Widget = Vanilla JS** — zero dependencies, no jQuery, < 30KB gzip
6. **Free tier = full features** — only difference is own Firebase vs hosted
7. **No IAP in mobile app** — all billing through adventchat.com web checkout
