# AdventChat — Self-Hosted Setup Guide

Complete step-by-step guide for setting up AdventChat with your own Firebase project (free tier).

---

## Prerequisites

- WordPress 6.0+ with PHP 8.1+
- A Google account (for Firebase)
- AdventChat plugin activated on your WordPress site

---

## Part 1: Create a Firebase Project

1. Go to **https://console.firebase.google.com**
2. Click **"Create a project"** (or **"Add project"**)
3. Enter a project name (e.g. `advent-chat`)
4. Accept or decline Google Analytics → click **"Create project"**
5. Wait for project to be created, then click **"Continue"**

---

## Part 2: Create a Firestore Database

1. In the Firebase Console left sidebar, click **"Databases and storage"** to expand
2. Click **"Firestore"**
3. Click **"Create database"**
4. Choose a database location closest to your users (e.g. `europe-west1` for EU, `us-central1` for US)
5. Select **"Start in test mode"** (we will add proper security rules later)
6. Click **"Create"**

Your Firestore database is now ready.

---

## Part 3: Enable Authentication

1. In the left sidebar, click **"Authentication"** (under Project shortcuts)
   - If not visible, use the **"Search for products"** bar and type `Authentication`
2. Click **"Get started"**
3. Under **"Sign-in method"** tab, click **"Google"**
4. Toggle the **Enable** switch ON
5. Choose a project support email from the dropdown
6. Click **"Save"**

> **Note:** Apple Sign-In is only needed for the iOS mobile app. Skip it for web-only setups.

---

## Part 4: Get Your Firebase Web App Config

1. Click the **⚙️ Settings gear** icon (top-left) → **"Project settings"**
2. Stay on the **"General"** tab
3. Scroll down to **"Your apps"** section
4. If no web app exists yet:
   - Click the **`</>`** (Web) icon
   - Enter app nickname: `AdventChat Web`
   - Leave "Firebase Hosting" unchecked
   - Click **"Register app"**
5. You will see a code block with your config. **Copy the config object only** — the part that looks like:

```json
{
  "apiKey": "AIzaSy...",
  "authDomain": "your-project.firebaseapp.com",
  "projectId": "your-project-id",
  "storageBucket": "your-project.appspot.com",
  "messagingSenderId": "123456789",
  "appId": "1:123456789:web:abcdef"
}
```

6. Keep this copied — you'll paste it into WordPress next.

---

## Part 5: Configure AdventChat in WordPress

### ⚠️ **CRITICAL: This Step Must Be Done First**

**The entire AdventChat setup depends on a working Firebase configuration. Do this step BEFORE configuring anything else.**

---

### 5.1 — Firebase Tab (MUST DO THIS FIRST)

1. In WordPress admin, go to **AdventChat → Settings**
2. Click the **"Firebase"** tab
3. **Paste your Firebase config JSON** (from Part 4) into the **"Web App Config (JSON)"** textarea
4. **Click the blue "Save & Test Connection" button** below the textarea
   - **Important:** This is NOT the page-wide "Save Changes" button — it's the dedicated Firebase button
   - This button does two things at once:
     - ✅ Saves your config (encrypted for security)
     - ✅ Tests the connection to Firebase
5. Wait for the result:
   - **Green success message ("✓ Saved and connected!")** → Continue to next section
   - **Red error message** → See Troubleshooting below

**Visual Guide:**
```
[Web App Config (JSON) textarea]
Your Firebase config should be here
[or it may appear empty after save — this is normal]

"Paste the full Firebase config object: { apiKey, authDomain, projectId, ... }"

[Save & Test Connection]  ← Click this button, NOT the page-wide ones
✓ Saved and connected!    ← You'll see this in green
```

### 5.2 — Firestore Security Rules

Still on the Firebase tab, scroll down to **"Firestore Security Rules"**:

1. Click the **"Copy Rules"** button (top-right of the code block)
2. Go to **Firebase Console** → **Firestore Database** → **"Rules"** tab (at the very top)
3. Select all existing rules and **delete them**
4. **Paste the rules you copied** from WordPress
5. Click **"Publish"**

### 5.3 — General Tab

Go to AdventChat → Settings → **"General"** tab:

| Field | What to enter | Default |
|-------|--------------|---------|
| **Welcome Title** | Greeting visitors see when widget opens | `Hi there! 👋` |
| **Welcome Subtitle** | Secondary text below the title | `How can we help you?` |
| **Input Placeholder** | Ghost text in the message input | `Type a message…` |

Click **"Save Changes"**.

### 5.4 — Appearance Tab

Go to **"Appearance"** tab:

| Field | What to set | Default |
|-------|------------|---------|
| **Primary Color** | Your brand color (click the picker) | `#0066ff` |
| **Secondary Color** | Background/accent color | `#ffffff` |
| **Widget Position** | Bottom Right or Bottom Left | `Bottom Right` |
| **Horizontal Offset** | Pixels from the side edge | `20` |
| **Vertical Offset** | Pixels from the bottom edge | `20` |
| **Launcher Style** | Bubble / Tab / Custom Image | `Bubble` |
| **Launcher Image URL** | Only if using "Custom Image" style | (empty) |
| **Custom CSS** | Advanced: override widget CSS | (empty) |

Click **"Save Changes"**.

### 5.5 — Display Rules Tab

Go to **"Display Rules"** tab:

| Field | Options | Recommendation |
|-------|---------|---------------|
| **Visibility Mode** | Show on all pages / Show only on specific pages / Hide on specific pages | `Show on all pages` to start |
| **Page IDs** | Comma-separated page/post IDs | Leave blank for "all pages" mode |
| **Post Types** | Comma-separated (e.g. `page,product`) | Leave blank for all |
| **User Roles** | Comma-separated (e.g. `subscriber`) | Leave blank for all visitors |
| **Hide on Mobile** | Checkbox | Uncheck (show on mobile) |
| **Guest Only** | Checkbox | Uncheck (show to everyone) |

Click **"Save Changes"**.

### 5.6 — Chat Tab

Go to **"Chat"** tab:

| Field | What it does | Recommended |
|-------|-------------|-------------|
| **Sound Notifications** | Play a sound for new messages | ✅ Enabled |
| **Auto-open Widget** | Widget opens automatically after delay | ❌ Disabled (can be annoying) |
| **Auto-open Delay** | Seconds before auto-open | `5` (only if auto-open is on) |
| **Chat Routing** | How chats are assigned to agents | `Round Robin` (fair distribution) |
| **Email Transcript** | Let visitors email themselves the chat | ✅ Enabled |
| **Chat Rating (CSAT)** | Ask for a rating when chat ends | ✅ Enabled |
| **File Sharing** | Allow image/file uploads in chat | ✅ Enabled |

**Routing modes explained:**
- **Round Robin** — Chats go to agents in rotation (best for teams)
- **Manual** — Agents must click "Accept" to take a chat
- **Notify All** — All agents are notified; first to accept gets it

Click **"Save Changes"**.

### 5.7 — Offline Tab

Go to **"Offline"** tab:

| Field | What to set |
|-------|------------|
| **Enable Offline Form** | ✅ Check this — shows a contact form when no agents are online |
| **Notification Email** | Email address that receives offline messages (defaults to your admin email) |

Click **"Save Changes"**.

### 5.8 — Privacy Tab

Go to **"Privacy"** tab:

| Field | What it does | Recommended |
|-------|-------------|-------------|
| **GDPR Consent** | Show consent checkbox before chat starts | ✅ Enable if you have EU visitors |
| **Pre-chat Form** | Require name + email before chatting | ✅ Enabled (helps identify visitors) |
| **Privacy Policy Page** | Select your Privacy Policy page from dropdown | Select your privacy page |

Click **"Save Changes"**.

---

## Part 6: Verify the Chat Widget on Your Site

1. Open your website in a **new browser tab** (or incognito window)
2. You should see a **chat bubble** in the bottom-right corner (or wherever you positioned it)
3. Click it → the chat widget opens with your welcome title/subtitle
4. If Pre-chat Form is enabled, you'll see name + email fields
5. Type a message and send it

---

## Part 7: Accept Chats as an Operator

1. In WordPress admin, go to **AdventChat → Live Chat**
2. You'll see the operator console
3. When a visitor starts a chat, it appears in the **"Waiting"** tab
4. Click **"Accept"** to start chatting
5. Type your reply and hit Enter or click Send

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| **"Save & Test Connection" button does nothing** | Make sure your JSON is valid; check browser console for errors (F12) |
| **Red error: "Invalid JSON"** | Your Firebase config may have typos or be malformed. Copy it fresh from Firebase Console |
| **Red error: "Missing required key"** | Your config is missing one of: `apiKey`, `authDomain`, or `projectId`. Verify the config is complete |
| **Config textarea appears empty after save** | This is normal — the config is encrypted for security. It's still there. |
| **Widget doesn't appear on site** | Check Display Rules tab — make sure visibility mode is "Show on all pages" |
| **Chat messages not appearing** | Verify Firestore security rules are published in Firebase Console |
| **Session exists in Firebase but Live Chat shows "No active chats"** | Refresh after sign-in. If browser console says index is building, wait until index status is Ready. Newer plugin builds avoid this index dependency automatically. |
| **Red console errors from `core.min.js`, `metaboxes.min.js`, or other plugins** | These are usually unrelated WordPress/plugin script warnings. Focus on AdventChat lines such as `[Console] Sessions listener error` and Firebase auth/query errors. |
| **Offline Messages page is empty** | Offline messages are stored in WordPress DB and only when offline form is submitted. Regular live chats are in Firebase `sessions` and do not appear in Offline Messages. |
| **Offline form not showing** | Enable it in the Offline tab; also check that agents are offline |
| **GDPR consent not showing** | Enable it in the Privacy tab |

---

## What's Next?

- **Mobile App** — Set up the AdventChat mobile operator app for iOS/Android → see [Mobile Setup Guide](mobile-setup.md)
- **Pro Hosting** — Upgrade to hosted Firebase infrastructure → see [Pro Hosting Guide](pro-hosting-setup.md)
