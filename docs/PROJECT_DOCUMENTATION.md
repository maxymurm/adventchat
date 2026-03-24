# AdventChat — WordPress Plugin Documentation

**Last Updated:** 2026-03-23  
**Current Phase:** Phase 0 — Project Initialization & Scoping  
**Status:** 🟡 Planning

---

## 📊 Project Overview

### Description
AdventChat is a freemium WordPress live chat plugin that uses Firebase Realtime Database as its real-time backbone. Free tier users bring their own Firebase project (full data ownership). Paid tier users get hosted Firebase infrastructure, priority support, and access to the AdventChat Mobile app for managing chats from iOS and Android devices.

### Technology Stack
- **Backend:** PHP 8.1+ / WordPress 6.0+
- **Real-time:** Firebase Realtime Database
- **Auth:** Firebase Custom Tokens (PHP JWT signing)
- **Frontend Widget:** Vanilla JS (zero-dependency visitor chat widget)
- **Operator Console:** React/TypeScript SPA (WP Admin embedded)
- **Database:** WordPress database (offline messages, logs, macros) + Firebase (live chat)
- **Mobile:** Ionic 8 + Angular 18 + Capacitor 6 (separate repo: adventchat-mobile)
- **CI/CD:** GitHub Actions (TBD)

### Repository
- **GitHub:** TBD
- **Project Board:** TBD
- **Plugin Homepage:** TBD
- **Documentation Site:** TBD

### Team
- **Developer:** Maxwell Murunga (@maxymurm)
- **Stack Advisors:** Reference plugins (YITH, screets, LiveChat)

---

## 🎯 Phase Breakdown

### Phase 0: Project Initialization & Scoping ⏳ IN PROGRESS
**Timeline:** 2026-03-23 — TBD  
**Status:** In progress

**Tasks Completed:**
- ✅ 0.1: Read and understood all reference plugin architectures
- ✅ 0.2: Created project folder structure (docs/, .github/instructions/)
- ✅ 0.3: Created adventchat-mobile project folder
- ✅ 0.4: Copied agent docs to mobile project
- ✅ 0.5: Created Windows junctions to all 3 reference plugins
- ✅ 0.6: Created ecosystem.md for both projects
- ✅ 0.7: Created PROJECT_DOCUMENTATION.md

**Tasks Pending:**
- [ ] 0.8: Answer clarifying questions → finalize feature spec
- [ ] 0.9: Initialize Git repositories (WP plugin + mobile app)
- [ ] 0.10: Create GitHub repos + project boards
- [ ] 0.11: Create all Phase 1–N GitHub milestones
- [ ] 0.12: Create all GitHub issues from scope

---

### Phase 1: Core Foundation ⏳ UPCOMING
**Status:** Scoping in progress

**Planned tasks (subject to change after clarifying questions):**
- [ ] Plugin bootstrap / main class architecture
- [ ] Plugin settings framework (tabbed admin)
- [ ] Firebase configuration (Project ID, API Key, Service Account)
- [ ] Firebase token server-side generation (PHP)
- [ ] Firebase database security rules system
- [ ] Plugin activation / deactivation hooks

---

### Phase 2: Real-Time Chat Engine ⏳ UPCOMING
**Planned tasks:**
- [ ] Firebase Realtime Database schema design
- [ ] Visitor-side chat widget (Vanilla JS)
- [ ] Operator console SPA (React)
- [ ] Chat session lifecycle (init → active → ended)
- [ ] Typing indicators
- [ ] Read receipts
- [ ] Visitor user object (browser, OS, IP, page URL)

---

### Phase 3: Operator Features ⏳ UPCOMING
**Planned tasks:**
- [ ] WordPress operator role (`adventchat_operator` capability)
- [ ] Multi-agent support
- [ ] Chat assignment / routing
- [ ] Canned responses (macros) as WP custom post type
- [ ] Visitor queue management
- [ ] Chat transfer between agents
- [ ] Chat notes (internal)
- [ ] Departments

---

### Phase 4: Visitor Features ⏳ UPCOMING
**Planned tasks:**
- [ ] Pre-chat form (name, email — optional)
- [ ] Offline message form (when no operators online)
- [ ] Chat transcript request (email)
- [ ] Chat rating / CSAT
- [ ] File sharing (images/PDF)
- [ ] GDPR consent checkbox

---

### Phase 5: Display & Appearance ⏳ UPCOMING
**Planned tasks:**
- [ ] Color theme customizer
- [ ] Widget position (X/Y, bottom-right/left)
- [ ] Button style (bubble, tab, custom image)
- [ ] Show/hide rules (per page, post type, user role)
- [ ] Mobile hide option
- [ ] Guest hide option
- [ ] Auto-open with delay option
- [ ] Custom CSS support

---

### Phase 6: Integrations ⏳ UPCOMING
**Planned tasks:**
- [ ] WooCommerce — cart contents display, order context
- [ ] WPML / Polylang multilingual support
- [ ] Elementor widget / block
- [ ] Email notifications (offline messages, transcript)
- [ ] Webhook support for chat events

---

### Phase 7: Premium / Paid Tier ⏳ UPCOMING
**Planned tasks:**
- [ ] License key system
- [ ] Hosted Firebase provisioning API
- [ ] Subscription management integration
- [ ] Premium feature gating
- [ ] AdventChat account dashboard

---

### Phase 8: Mobile App Integration ⏳ UPCOMING
**Planned tasks:**
- [ ] Firebase cross-device presence (web + mobile)
- [ ] Mobile agent authentication flow
- [ ] Push notification dispatch (FCM)
- [ ] Chat handoff between web console and mobile app

---

## 🏗️ Architecture Decisions

### Firebase Architecture (from YITH analysis)
- **Realtime DB structure:** sessions/{siteId}/{sessionId}/messages/
- **Auth model:** PHP generates Firebase custom tokens for both visitors AND operators
- **Security rules:** Plugin provides a JSON rules snippet for the admin to paste into Firebase console
- **Credentials stored:** In `wp_options` table, encrypted

### WordPress Plugin Architecture
- **Pattern:** OOP singleton classes (following WordPress standards)
- **Admin framework:** Custom tabbed settings (similar to YITH plugin framework)
- **Operator console:** Separate menu page loading a bundled JS SPA
- **Widget:** Enqueued in `wp_footer`, zero external dependencies
- **Custom Post Types:** `adventchat_macro` (canned responses)
- **DB Tables:** `adventchat_offline` (offline messages), `adventchat_logs` (chat logs)

### API Design
- **Style:** REST API for SPA-to-PHP communication (operator console)
- **Authentication:** WordPress nonces + WP REST API authentication
- **Firebase bridge:** PHP generates tokens via Service Account private key (RS256)

---

## 📝 Change Log

### 2026-03-23

#### 14:00 — Project Initialization
**Type:** Documentation  
**Changes:**
- Created project folder structure
- Created ecosystem.md
- Created PROJECT_DOCUMENTATION.md (this file)
- Created adventchat-mobile project folder
- Copied agent docs to mobile project
- Created junction links to 3 reference plugins in both project folders

---

## 🧩 Key Features Inventory

*(Consolidated from all 3 reference plugin analyses)*

### Must Have (MVP)
- [ ] Firebase Realtime Database integration (user's own or hosted)
- [ ] 3-step Firebase setup wizard (Project ID, API Key, Service Account JSON)
- [ ] Visitor chat widget (embedded JS)
- [ ] Operator console (WP Admin page, SPA)
- [ ] Real-time messaging
- [ ] Typing indicators
- [ ] Operator online/offline status
- [ ] Offline message form + email notification
- [ ] Basic appearance settings (color, position)
- [ ] Free vs paid tier feature gating
- [ ] GDPR consent checkbox

### Should Have
- [ ] Canned responses / macros
- [ ] Multi-agent support
- [ ] Chat logs (admin list table)
- [ ] Chat transcript email
- [ ] Chat rating (1–5 stars)
- [ ] Pre-chat form
- [ ] WooCommerce integration (cart context)
- [ ] File sharing (images)
- [ ] Mobile push notifications (paid tier)

### Could Have (V2+)
- [ ] Chatbot / auto-responses
- [ ] Chat routing rules (round-robin, skill-based)
- [ ] Custom chat widget branding (white label)
- [ ] Visitor tracking (page history in session)
- [ ] Email marketing integration (add visitor to list)
- [ ] Analytics dashboard
- [ ] Voice messages

---

*This document is updated after every significant change. See Change Log section above.*
