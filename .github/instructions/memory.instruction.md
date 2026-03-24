---
applyTo: '**'
lastUpdated: '2026-03-24 15:00'
chatSession: 'session-004'
projectName: 'AdventChat WordPress Plugin'
---

# Project Memory — AdventChat WordPress Plugin

> **AGENT INSTRUCTIONS:** Always read this file FIRST before starting any new conversation. Update after completing tasks, making decisions, or when user says "remember this".

---

## 🎯 Current Focus

**Active Phase:** Phase 2 — Firebase & Firestore  
**Active Issue:** WP-20 (EPIC), start with WP-21  
**Current Branch:** develop  
**Last Commit:** `6613f37` — merge Phase 1 to develop  
**Last Activity:** Phase 1 COMPLETE — all 11 issues (#9-#19) implemented and closed. Milestone 2 closed.

**Phase 0 Complete Checklist:**
- ✅ Git repos, labels, milestones, boards, 90 issues, planning docs

**Phase 1 Complete Checklist:**
- ✅ adventchat.php — main plugin file with WP header, constants, bootstrap
- ✅ class-adventchat.php — singleton bootstrap, hooks, admin menu, REST init
- ✅ class-adventchat-activator.php — dbDelta creates offline_messages + chat_logs tables
- ✅ class-adventchat-deactivator.php + uninstall.php — cleanup and table drops
- ✅ class-adventchat-settings.php — 6-tab settings framework (General, Firebase, Appearance, Chat, Offline, Privacy)
- ✅ class-adventchat-options.php — get/set/delete with AES-256-CBC encryption for sensitive keys
- ✅ class-adventchat-roles.php — adventchat_operator role + admin capability
- ✅ class-adventchat-autoloader.php — file-exists based autoloading across includes/ subdirs
- ✅ class-adventchat-api-controller.php — REST base with public/operator/admin permission callbacks
- ✅ package.json + vite.config.ts + tsconfig.json + composer.json — esbuild widget + Vite console + Jest
- ✅ Build passes: `npm run build` succeeds (widget 163B, console 143KB gzip 45KB)

**Next Steps:**
1. `git checkout -b feature/phase-2-firebase develop`
2. Start with WP-21: Firestore schema documentation
3. Follow: WP-21 → WP-22 → ... → WP-28
4. Close EPIC #20 when all done, close milestone 3

---

## 👤 User Preferences — FINALIZED

### Project Identity
- **Plugin slug:** `adventchat`
- **Mobile app:** `adventchat-mobile`
- **GitHub username:** `maxymurm`
- **WP Plugin repo:** `maxymurm/adventchat` — PUBLIC (GPL v3)
- **Mobile repo:** `maxymurm/adventchat-mobile` — PRIVATE (closed source)
- **Project boards:** SEPARATE — one per project
- **Target launch:** WP plugin soft-beta ~week 6, public launch ~week 8, mobile app ~week 10-12

### Tech Stack — WP Plugin
- **PHP:** 8.1+ minimum | **WordPress:** 6.0+ minimum
- **Real-time:** Cloud Firestore (NOT Realtime DB — see decision below)
- **Firebase Auth:** Anonymous auth (visitors), Email/Password (agents via REST API)
- **Free tier setup:** Paste Web App Config JSON only — NO service account key required
- **Operator console:** React + TypeScript SPA embedded in WP Admin
- **Widget:** Vanilla JS, zero external dependencies, responsive/mobile-first
- **License:** GPL v3, WordPress.org submission

### Tech Stack — Mobile App
- **Framework:** Ionic 8 + Angular 18 (standalone components)
- **Native:** Capacitor 6 (iOS + Android simultaneously from day 1)
- **Push:** Firebase Cloud Messaging — hard requirement, must work backgrounded/killed
- **Auth:** AdventChat account API + Firebase custom tokens
- **Social login:** Apple Sign-In + Google Sign-In (V1, not later)
- **Audience:** Operators/agents ONLY (not visitors)
- **License:** Closed source (paid tier only)

### Business Model — FINALIZED
| Tier | Price | What's included |
|------|-------|-----------------|
| **Free** | $0 forever | Full plugin, own Firebase, unlimited sites, ALL features |
| **Pro** | $24/mo or $199/yr | Hosted Firebase, mobile app, priority support, 1 workspace |
| **Agency** | $59/mo or $499/yr | Unlimited client workspaces, white-label, all Pro features |

**Workspace = one Firebase database = unlimited WP sites pointing to it**

### Account/Subscription Backend — FINALIZED
- **Billing platform:** Lemon Squeezy (VAT-compliant, license keys, webhooks)
- **Validation:** Simple PHP REST API on adventchat.com
  - `POST /wp-json/adventchat/v1/validate-license`
  - Returns: `{ valid, plan, expires_at, firebase_config }` 
- **Mobile purchases:** Web-only checkout on adventchat.com (no in-app purchase, no 30% Apple/Google cut)

### MVP Scope — ALL of these in v1.0:
- Pre-chat form (name + email, operator-configurable)
- File/image sharing
- Chat routing: round-robin default, configurable (manual, all-notify, skill-based)
- Departments
- WooCommerce cart context
- Responsive mobile-first widget
- Canned responses (macros), GDPR consent, offline form, chat rating
- Chat logs, chat transcript email

### UI/UX
- Widget: LiveChat.com-style visitor experience
- Console: screets-style SPA in WP Admin + best from all 3 references
- Mobile: WhatsApp-style (bubbles, typing indicators, presence, read receipts)
- Dark mode: Day 1 (both plugin and mobile)
- Branding: TBD (placeholder logo for now)

### Git Workflow
- **Strategy:** main / develop / feature/issue-N-description
- **Commits:** Conventional commits + "Closes #N"
- **Auto-push:** After every commit

---

## 📁 Project File Map

```
adventchat/ (WP plugin root — C:\Users\maxmm\Local Sites\...\plugins\adventchat\)
├── agents/                     ← AI agent template docs (14 files, portable)
├── _references/                ← Junction links (gitignored)
│   ├── yith-live-chat          → YITH (Firebase architecture reference)
│   ├── screets-chat            → screets (console UI/SPA reference)
│   └── livechat                → LiveChat.com (widget UX reference)
├── docs/
│   ├── PROJECT_DOCUMENTATION.md
│   ├── OPUS_SCOPING_PROMPT.md  ← ⭐ THE OPUS PROMPT — paste into Claude Opus to execute
│   ├── planning/
│   ├── architecture/
│   ├── api/
│   └── guides/
├── .github/
│   ├── instructions/
│   │   └── memory.instruction.md (this file)
│   └── ISSUE_TEMPLATE/
├── .gitignore                  ← excludes _references/
├── ecosystem.md
└── [source code — not yet created]
```

### Reference Plugin Key Files
| Purpose | File |
|---------|------|
| WP plugin OOP architecture | `_references/yith-live-chat/class-yith-livechat.php` |
| Settings framework pattern | `_references/yith-live-chat/includes/class-ylc-settings.php` |
| Firestore rules reference | `_references/yith-live-chat/assets/rules.json` |
| User/visitor object pattern | `_references/yith-live-chat/includes/class-ylc-user.php` |
| Macro CPT pattern | `_references/yith-live-chat/includes/class-ylc-macro.php` |
| Console SPA embedding | `_references/screets-chat/core/admin.php` |
| Widget injection pattern | `_references/screets-chat/screets-chat.php` |
| Widget UX + WooCommerce | `_references/livechat/includes/plugin.php` |

---

## 💭 Decisions Made & Rationale

### 2026-03-24

#### Cloud Firestore (NOT Realtime DB)
**Decision:** Cloud Firestore is the real-time backend  
**Rationale:** Better data model (collections/subcollections = natural chat hierarchy), better querying for history/logs, more expressive security rules, native offline support for Capacitor mobile app, Google's current product (Realtime DB is legacy). Latency difference is imperceptible for chat.

#### Anonymous Auth for visitors — no service account needed
**Decision:** Visitors use Firebase Anonymous Auth; agents use Firebase Email/Password  
**Rationale:** Plugin auto-creates a Firebase email account per WP operator via Firebase Auth REST API. Only Web App Config JSON needed from user (6 safe fields, NOT the service account private key). This is dramatically simpler than YITH's approach and eliminates the biggest setup friction point.

#### Lemon Squeezy over WooCommerce Subscriptions
**Decision:** Lemon Squeezy for billing  
**Rationale:** WooCommerce Subscriptions ties the SaaS business to one WP install. Lemon Squeezy handles global VAT automatically (legally required), has built-in license keys, is webhook-based. Integration = one endpoint.

#### Per-workspace pricing (unlimited sites)
**Decision:** Subscription = workspace, not per-site  
**Rationale:** Per-site pricing punishes power users. "Unlimited sites per workspace" is the headline sell. Revenue scales via plan tier upgrades, not site count.

#### Web-only purchases (no mobile in-app purchase)
**Decision:** Users subscribe on adventchat.com; mobile app is a free download  
**Rationale:** Subscription is for hosted Firebase infrastructure (B2B service, not in-app content). Avoids 30% Apple/Google commission.

---

## 🔧 Critical Notes for Future Agents

- Path has spaces: always quote `"C:\Users\maxmm\Local Sites\..."` in PowerShell
- Reference plugins are nulled/pirated copies — study architecture ONLY, write all code from scratch
- `_references/` junctions are gitignored in both projects (see .gitignore)
- Plugin git repo must be scoped to plugin folder ONLY, not the Local WordPress installation root
- Firestore is partitioned by `siteId` — free tier = user's own Firebase project; paid tier = shared AdventChat Firebase project with siteId isolation
- The Opus prompt (`docs/OPUS_SCOPING_PROMPT.md`) covers BOTH the WP plugin and the mobile app repos

---

## 📊 Firestore Data Model (Finalized)

```
/sites/{siteId}
  name, domain, ownerId, plan, createdAt

/sites/{siteId}/sessions/{sessionId}
  status: "waiting"|"active"|"ended"
  assignedAgentId, departmentId
  startedAt, endedAt
  visitorInfo: { name, email, page, browser, os, ip, country }
  wooContext: { cartTotal, cartItems[], currentOrderId } | null
  rating: 1-5 | null, transcriptRequested: bool

/sites/{siteId}/sessions/{sessionId}/messages/{messageId}
  text, type: "text"|"image"|"file"|"system"
  fileUrl, fileName, fileMimeType
  senderId, senderType: "visitor"|"agent"
  sentAt (Timestamp), readAt (Timestamp|null)

/sites/{siteId}/agents/{agentId}
  wpUserId, displayName, email, avatar
  status: "online"|"away"|"offline"
  lastSeen (Timestamp), fcmTokens[], departmentIds[]

/sites/{siteId}/departments/{deptId}
  name, agentIds[], isDefault

/sites/{siteId}/macros/{macroId}
  title, body, agentId (null=shared), createdAt

/sites/{siteId}/typing/{sessionId}
  agentTyping: bool, visitorTyping: bool, updatedAt

/sites/{siteId}/offlineMessages/{msgId}
  name, email, message, page, departmentId, sentAt, read, readAt
```
