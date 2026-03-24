# AdventChat — Firestore Schema

> Complete Cloud Firestore data model for AdventChat live chat.

## Database Structure

All data is scoped under `/sites/{siteId}/` to support multi-site isolation.

### siteId Generation
- **Free tier:** `md5( get_site_url() )` — derived from the WordPress site URL
- **Paid tier:** Provisioned UUID from `adventchat.com` when license is activated

---

## Collections

### `/sites/{siteId}/sessions/{sessionId}`

Active and historical chat sessions.

| Field | Type | Description |
|-------|------|-------------|
| `status` | string | `waiting` \| `active` \| `ended` |
| `assignedAgentId` | string | Firebase UID of the assigned agent (empty if waiting) |
| `visitorAnonUid` | string | Firebase Anonymous Auth UID of the visitor |
| `visitorInfo` | map | `{ name, email, pageUrl, pageTitle, browser, os, device, ip, wpUserId, wcCartTotal }` |
| `department` | string | Department slug (empty = default) |
| `startedAt` | timestamp | When the session was created |
| `endedAt` | timestamp \| null | When the chat ended |
| `rating` | number | 0–5 CSAT rating (0 = unrated) |
| `ratingComment` | string | Optional feedback text |
| `gdprConsent` | boolean | Whether GDPR consent was given |
| `transferredFrom` | string \| null | Previous agent UID if chat was transferred |
| `lastMessageAt` | timestamp | Updated on each new message for sorting |

**Indexes:**
- `status` + `startedAt` (composite) — for the visitor queue
- `assignedAgentId` + `status` — for agent's active chats

---

### `/sites/{siteId}/sessions/{sessionId}/messages/{messageId}`

Individual chat messages within a session.

| Field | Type | Description |
|-------|------|-------------|
| `text` | string | Message content (plain text or markdown) |
| `senderType` | string | `visitor` \| `agent` \| `system` |
| `senderId` | string | Firebase UID of sender |
| `senderName` | string | Display name |
| `sentAt` | timestamp | When sent |
| `readAt` | timestamp \| null | When read by recipient |
| `type` | string | `text` \| `image` \| `file` \| `internal` \| `system` |
| `fileUrl` | string \| null | Firebase Storage URL (for image/file types) |
| `fileName` | string \| null | Original filename |
| `fileSize` | number \| null | File size in bytes |

---

### `/sites/{siteId}/agents/{agentId}`

Operator/agent presence and metadata. `agentId` = Firebase Auth UID.

| Field | Type | Description |
|-------|------|-------------|
| `displayName` | string | Agent's display name |
| `email` | string | Agent's email |
| `avatarUrl` | string | Profile image URL |
| `fcmToken` | string | FCM push notification token (from mobile app) |
| `status` | string | `online` \| `away` \| `offline` |
| `lastSeen` | timestamp | Last heartbeat |
| `activeChats` | number | Count of active sessions |
| `departments` | array\<string\> | Department slugs agent belongs to |

---

### `/sites/{siteId}/departments/{deptId}`

Chat departments for routing.

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Department display name |
| `slug` | string | URL-safe identifier |
| `agentIds` | array\<string\> | Firebase UIDs of agents in this department |
| `isDefault` | boolean | Whether this is the default department |

---

### `/sites/{siteId}/macros/{macroId}`

Canned responses / quick replies.

| Field | Type | Description |
|-------|------|-------------|
| `title` | string | Short label for quick identification |
| `body` | string | Full macro text to insert |
| `scope` | string | `personal` \| `shared` |
| `agentId` | string | Creator's Firebase UID (for personal macros) |
| `createdAt` | timestamp | Creation date |
| `updatedAt` | timestamp | Last modification |

---

### `/sites/{siteId}/typing/{sessionId}`

Real-time typing indicators (one document per active session).

| Field | Type | Description |
|-------|------|-------------|
| `visitorTyping` | boolean | Whether visitor is currently typing |
| `agentTyping` | boolean | Whether agent is currently typing |
| `updatedAt` | timestamp | Last update (for stale detection, >5s = not typing) |

---

### `/sites/{siteId}/offlineMessages/{id}`

Messages left when no agents are online (mirrored to WP DB).

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Visitor name |
| `email` | string | Visitor email |
| `message` | string | Message text |
| `department` | string | Target department |
| `pageUrl` | string | Page visitor was on |
| `readAt` | timestamp \| null | When an agent read it |
| `createdAt` | timestamp | When submitted |

---

## Security Rules Summary

See `assets/firestore.rules` for the complete rules file.

**Key principles:**
1. All reads/writes require `request.auth != null`
2. Visitors can only access their own sessions (matched by `visitorAnonUid`)
3. Agents can access all sessions for their site (verified by agent document existence)
4. Typing documents are read/write for any authenticated user
5. Macros are readable by all authenticated users, writable only by agents
6. Agents can only write their own agent document
