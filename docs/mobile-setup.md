# AdventChat — Mobile App Setup Guide

> **Status:** Coming soon. This guide will be completed when the mobile app is released.

---

## Overview

The AdventChat mobile app lets operators manage live chats from their phone (iOS and Android). It connects to the same Firebase project as your WordPress site.

---

## Prerequisites

- AdventChat plugin configured and working on your WordPress site (see [Self-Hosted Setup Guide](self-hosted-setup.md))
- Firebase project with Authentication enabled
- iOS device (iPhone/iPad) or Android device

---

## Part 1: Firebase Setup for Mobile

### 1.1 — Enable Google Sign-In (already done if you followed self-hosted setup)

Your Firebase project should already have Google Sign-In enabled from the web setup.

### 1.2 — Enable Apple Sign-In (required for iOS App Store)

1. Go to Firebase Console → **Authentication** → **Sign-in method**
2. Click **"Apple"** → toggle **Enable**
3. You will need:
   - **Apple Team ID** — found at https://developer.apple.com/account → Membership
   - **Services ID** — create one in Apple Developer → Certificates, Identifiers & Profiles
   - **Key ID + Private Key** — create a Sign in with Apple key
4. Enter these values and click **"Save"**

### 1.3 — Cloud Messaging (FCM) Sender ID

1. Go to Firebase Console → **⚙️ Settings** → **"Cloud Messaging"** tab
2. Note your **Sender ID** (the numeric ID shown under "Firebase Cloud Messaging API (V1)")
3. This is used by the mobile app to receive push notifications

---

## Part 2: Install the Mobile App

*Instructions will be added once the app is published to the App Store and Google Play.*

### iOS
1. Open the App Store
2. Search for **"AdventChat"**
3. Download and install

### Android
1. Open Google Play Store
2. Search for **"AdventChat"**
3. Download and install

---

## Part 3: Connect to Your Site

1. Open the AdventChat app
2. Enter your **WordPress site URL** (e.g. `https://yoursite.com`)
3. Tap **"Connect"**
4. Sign in with **Google** or **Apple**
5. You should see the chat inbox

---

## Part 4: Receiving Push Notifications

1. Allow push notifications when prompted
2. You'll receive notifications for:
   - New incoming chat requests
   - New messages in active chats
   - Escalation alerts
3. You can reply directly from the notification (Quick Reply)

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| **Can't connect to site** | Verify your WordPress URL is correct and the AdventChat plugin is active |
| **Sign-in fails** | Make sure Firebase Authentication has Google (and Apple for iOS) enabled |
| **No push notifications** | Check device notification settings; verify FCM is enabled in Firebase |

---

*This guide will be expanded with screenshots and detailed steps upon app release.*
