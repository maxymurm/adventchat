# AdventChat — Ecosystem & Reference Map

**Last Updated:** 2026-03-23  
**Project:** AdventChat WordPress Plugin  
**Repository:** TBD (to be configured during project initialization)

---

## 🗺️ Project Ecosystem

This document maps all components, repositories, and reference materials for the AdventChat project ecosystem.

---

## 📦 AdventChat Projects

| Project | Type | Location | Repo |
|---------|------|----------|------|
| **adventchat** | WordPress Plugin (GPL) | `C:\Users\maxmm\Local Sites\...\plugins\adventchat` | TBD |
| **adventchat-mobile** | Ionic App (iOS + Android, closed source) | `C:\Users\maxmm\projects\adventchat-mobile` | TBD (private) |

---

## 🔗 Reference Plugins (Junction Links)

All reference plugins are accessible via directory junctions inside `_references/`.

| Junction | Source | Description |
|----------|--------|-------------|
| `_references/yith-live-chat` | `C:\Users\maxmm\Local Sites\...\plugins\yith-live-chat-premium` | YITH Live Chat Premium — Firebase-backed, best WP admin architecture |
| `_references/screets-chat` | `C:\Users\maxmm\projects\livechat\screets-chat-3.7.6\screets-chat\screets-chat` | screets Chat — best operator console UI/UX |
| `_references/livechat` | `C:\Users\maxmm\projects\livechat\wp-live-chat-software-for-wordpress` | LiveChat.com WP plugin — best frontend widget UX |

### Junction Management

Junctions are Windows directory junctions (no symlink admin rights needed).  
They are added to `.gitignore` to avoid committing reference plugin code.

**Recreate junctions (run as needed):**
```powershell
# From project root
$thisDir = "C:\Users\maxmm\Local Sites\advent-digest\app\public\wp-content\plugins\adventchat\_references"

cmd /c "mklink /J `"$thisDir\yith-live-chat`" `"C:\Users\maxmm\Local Sites\advent-digest\app\public\wp-content\plugins\yith-live-chat-premium`""
cmd /c "mklink /J `"$thisDir\screets-chat`" `"C:\Users\maxmm\projects\livechat\screets-chat-3.7.6\screets-chat\screets-chat`""
cmd /c "mklink /J `"$thisDir\livechat`" `"C:\Users\maxmm\projects\livechat\wp-live-chat-software-for-wordpress`""
```

---

## 🏗️ Tech Stack Overview

### WordPress Plugin (adventchat)
| Layer | Technology |
|-------|-----------|
| Language | PHP 8.1+ |
| Framework | WordPress (GPL) |
| Real-time Backend | Firebase Realtime Database |
| Auth | Firebase Custom Tokens (server-side JWT signing) |
| Admin UI | React/Vanilla JS operator console |
| Widget | Vanilla JS (zero-dependency visitor widget) |
| Licensing | GPL v3 (free + premium tiers) |
| Minimum WP | 6.0+ |
| Minimum PHP | 8.1 |

### Mobile App (adventchat-mobile)
| Layer | Technology |
|-------|-----------|
| Framework | Ionic 8 + Angular 18 (standalone components) |
| Native | Capacitor 6 (iOS + Android) |
| Real-time | Firebase Realtime Database + FCM Push |
| State | Angular Signals |
| Auth | Firebase Auth + WhatsApp-style presence |
| Storage | @capacitor/preferences (Keychain/Keystore) |
| Source | Closed source (commercial, paid tier only) |

---

## 🎯 Key Design Decisions from Reference Plugins

### From YITH Live Chat Premium ✅ (primary inspiration)
- Firebase Realtime Database as the real-time backbone
- User-friendly 3-credential Firebase setup (Project ID, API Key, Private Key)
- PHP generates Firebase custom tokens server-side
- Database security rules JSON provided to admin
- Chat operator role/capability system
- Macros (canned responses) as WordPress custom post type
- Offline messages stored in WP database
- Chat logs with WP_List_Table

### From screets Chat ✅ (UI inspiration)
- Clean, modern operator console SPA architecture
- Polylang + WPML full multilingual support
- HMAC identity verification for logged-in visitors
- WooCommerce order event integration

### From LiveChat.com WP Plugin ✅ (UX inspiration)
- Clean visitor-facing widget UX
- WooCommerce cart sync
- Zero-dependency frontend script approach

---

## 🔄 Freemium Model

| Feature | Free Tier | Pro ($24/mo or $199/yr) | Agency ($59/mo or $499/yr) |
|---------|-----------|-------------------------|---------------------------|
| Firebase backend | User's own Firebase project | AdventChat-hosted (zero setup) | AdventChat-hosted |
| Sites per workspace | Unlimited | Unlimited | Unlimited client workspaces |
| Mobile app | ❌ | ✅ iOS + Android | ✅ |
| Support | Community | Priority email + chat | Priority + SLA |
| White-label | ❌ | ❌ | ✅ |
| All chat features | ✅ Full | ✅ Full | ✅ Full |

**"Workspace" = one Firebase database.** Unlimited WP sites can point to the same workspace.

## 🔐 Architecture Decisions Made (2026-03-24)

### Firebase: Cloud Firestore (NOT Realtime DB)
- Better data model for chat (collection/document hierarchy)
- Better querying, security rules, offline support
- Google's current product vs Realtime DB (legacy)

### Firebase Auth Model
- **Visitors:** Firebase Anonymous Authentication
- **Agents:** Firebase Email/Password (auto-created by plugin via REST API)
- **Free tier setup:** Paste Web App Config JSON (6 fields, NOT sensitive service account key)
- **NO service account required** — simpler than YITH's approach

### Account/Subscription Backend
- **Billing:** Lemon Squeezy (not WooCommerce Subscriptions)
  - Handles global VAT automatically
  - Built-in license key system
  - Webhook-based subscription management
- **Validation API:** Simple REST endpoint on adventchat.com (PHP)
  - `POST /validate-license` → `{ valid: bool, plan: string, firebase_config: {...} }`
- **Mobile purchases:** Web-only checkout (avoids 30% Apple/Google tax)

### Operator Console
- **Tech:** React + TypeScript (embedded SPA in WP Admin)
- **UI inspiration:** screets Chat console + LiveChat widget combined

### GitHub Repos
- **WP Plugin:** `maxymurm/adventchat` — Public (GPL)
- **Mobile App:** `maxymurm/adventchat-mobile` — Private (closed source)
- **Separate project boards** (one per repo)

---

## 📂 Repository Structure (Final Layout)

```
adventchat/                          ← WordPress plugin (this project)
├── agents/                          ← AI agent automation docs
├── _references/                     ← Junction links to reference plugins (gitignored)
│   ├── yith-live-chat → ...
│   ├── screets-chat → ...
│   └── livechat → ...
├── docs/                            ← Project documentation
│   ├── PROJECT_DOCUMENTATION.md
│   ├── planning/                    ← Scoping docs and issue JSON
│   ├── architecture/                ← DB schema, Firebase structure
│   ├── api/                         ← PHP/REST API docs
│   └── guides/                      ← Setup guides, user docs
├── .github/
│   ├── instructions/
│   │   └── memory.instruction.md    ← Live project memory
│   └── ISSUE_TEMPLATE/
├── adventchat.php                   ← Plugin main file (to be created)
├── includes/                        ← PHP classes
├── assets/                          ← JS, CSS, images
├── templates/                       ← PHP templates
├── languages/                       ← i18n pot/po/mo files
└── ecosystem.md                     ← This file
```

---

## 🔗 Related Links

- WordPress Plugin Handbook: https://developer.wordpress.org/plugins/
- Firebase Realtime Database Docs: https://firebase.google.com/docs/database
- Ionic Framework: https://ionicframework.com/docs
- Capacitor: https://capacitorjs.com/docs

---

*This file is the source of truth for ecosystem relationships. Update when adding new projects or changing locations.*
