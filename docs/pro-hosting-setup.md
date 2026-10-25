# AdventChat — Pro Hosting Setup Guide

> **Status:** Coming soon. This guide will be completed when Pro hosting is available.

---

## Overview

AdventChat Pro provides **hosted Firebase infrastructure** so you don't need to create or manage your own Firebase project. Everything is provisioned automatically when you activate a Pro or Agency license.

---

## Prerequisites

- AdventChat plugin installed and activated on your WordPress site
- An AdventChat Pro or Agency license key (purchase at https://adventchat.com)

---

## Part 1: Purchase a License

1. Go to **https://adventchat.com/pricing**
2. Choose a plan:
   - **Pro** — Hosted Firebase, push notifications, priority support
   - **Agency** — Everything in Pro + multi-site management, white-label options
3. Complete the purchase
4. You'll receive a **license key** via email

---

## Part 2: Activate Your License

1. In WordPress admin, go to **AdventChat → Settings**
2. Look for the **License** section (or Account tab)
3. Paste your **license key**
4. Click **"Activate License"**
5. You should see a green **"License active"** confirmation

---

## Part 3: Automatic Firebase Provisioning

Once your license is active:

1. AdventChat automatically provisions a hosted Firebase project for your site
2. The **Firebase tab** in Settings will show **"Using AdventChat hosted infrastructure"**
3. No manual Firebase setup is needed — no config JSON to paste
4. Firestore security rules are managed automatically
5. Authentication providers are pre-configured

---

## Part 4: What's Different from Self-Hosted

| Feature | Self-Hosted (Free) | Pro Hosting |
|---------|-------------------|-------------|
| Operators | Unlimited | Unlimited |
| Firebase project | You create and manage | Managed for you |
| Firebase config | You paste JSON manually | Auto-configured |
| Security rules | You copy/paste manually | Auto-managed |
| FCM push notifications | Manual setup | Pre-configured |
| Firebase billing | Your Google account | Included in plan |
| Firestore limits | Firebase free tier (50K reads/day) | Higher limits included |
| Support | Community | Priority email support |

---

## Part 5: Mobile App with Pro

When using Pro hosting, the mobile app setup is simplified:

1. Install the AdventChat app (iOS/Android)
2. Enter your WordPress site URL
3. Sign in — the app auto-detects your hosted Firebase config
4. Push notifications work out of the box

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| **License activation fails** | Check the key for typos; ensure your site can reach adventchat.com |
| **Still showing Firebase config fields** | Clear your browser cache and reload the settings page |
| **"License expired"** | Renew at https://adventchat.com/account |
| **Session exists in Firebase but Live Chat shows no chats** | If Firestore index is still Building, wait until status becomes Ready. Recent AdventChat builds query without composite-index dependency. |
| **Console shows many red errors unrelated to AdventChat** | Errors from `core.min.js`/other plugins can appear on the same admin page; prioritize AdventChat/Firebase errors first. |

---

*This guide will be expanded with screenshots when Pro hosting launches.*
