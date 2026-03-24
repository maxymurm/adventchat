=== AdventChat – Live Chat for WordPress ===
Contributors: adventchat
Tags: live chat, chat, customer support, firebase, real-time
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Real-time live chat powered by Firebase. Free forever with your own Firebase, or upgrade for hosted infrastructure and a mobile operator app.

== Description ==

AdventChat is a modern, real-time live chat plugin for WordPress. Connect with your website visitors instantly using Firebase's powerful real-time infrastructure.

= Free Features =

* **Real-time Messaging** — Instant message delivery via Cloud Firestore.
* **Operator Console** — Full-featured admin dashboard with React 18.
* **Pre-chat Forms** — Collect visitor name and email before chat starts.
* **Offline Messages** — Accept messages when no agents are online.
* **Chat Ratings (CSAT)** — Collect customer satisfaction ratings.
* **File Sharing** — Share images and files via Firebase Storage.
* **Typing Indicators** — Real-time typing status for both sides.
* **Sound Notifications** — Audio alerts for new messages.
* **Chat Transcripts** — Email chat transcripts to visitors.
* **Departments** — Route chats to the right team.
* **Macros / Canned Responses** — Quick replies for common questions.
* **Display Rules** — Show/hide the widget by page, post type, user role, or device.
* **Custom Appearance** — Colors, position, launcher style, custom CSS.
* **GDPR Consent** — Optional consent checkbox.
* **WooCommerce Integration** — Cart context, customer identity, order info.
* **WPML / Polylang / Elementor** — Full compatibility.
* **REST API** — Widget config endpoint for headless setups.
* **Identity Verification** — HMAC-SHA256 for logged-in user verification.

= Premium Features =

* **Hosted Firebase** — No Firebase setup needed. One-click provisioning.
* **Mobile Push Notifications** — FCM push alerts to operator devices.
* **Analytics Dashboard** — Chat volume, ratings, response times with Chart.js.
* **Priority Support** — Direct support from the AdventChat team.

= How It Works =

1. Install and activate the plugin.
2. Create a free Firebase project (or upgrade for hosted infrastructure).
3. Paste your Firebase config in Settings → Firebase.
4. The chat widget appears on your site automatically.

= Requirements =

* WordPress 6.0+
* PHP 8.1+
* A Firebase project (free tier is sufficient)

== Installation ==

1. Upload the `adventchat` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to AdventChat → Settings → Firebase and enter your Firebase configuration.
4. Configure appearance and behavior in the other settings tabs.
5. Visit your site — the chat widget will appear!

= Firebase Setup =

1. Go to [Firebase Console](https://console.firebase.google.com/) and create a project.
2. Enable **Authentication** → Sign-in method → Anonymous.
3. Enable **Cloud Firestore** in production mode.
4. Copy your web app config (apiKey, authDomain, projectId, etc.).
5. Paste it in AdventChat → Settings → Firebase.

== Frequently Asked Questions ==

= Is AdventChat free? =

Yes! AdventChat is completely free when you use your own Firebase project. Firebase's free tier supports thousands of concurrent connections.

= Do I need a Firebase account? =

Yes, for the free version. You can create a Firebase project at no cost. Premium users can opt for hosted infrastructure with one-click setup.

= Does it work with WooCommerce? =

Yes. AdventChat automatically detects WooCommerce and includes cart contents, customer identity, and order information in the chat context.

= Does it work with page builders? =

Yes. AdventChat includes an Elementor widget and works with any theme or page builder.

= Is it GDPR compliant? =

AdventChat includes an optional GDPR consent checkbox and integrates with WPML/Polylang for multilingual privacy notices.

= Can I customize the appearance? =

Yes. Change colors, position, launcher style, offsets, and add custom CSS. You can also set display rules to control where the widget appears.

== Screenshots ==

1. Chat widget on the frontend — clean, modern design.
2. Operator console in wp-admin — three-column layout.
3. Settings page — appearance customization options.
4. Pre-chat form — collect visitor info before starting.
5. WooCommerce integration — cart context in chat.
6. Analytics dashboard widget — chat volume and ratings.

== Changelog ==

= 1.0.0 =
* Initial release.
* Real-time messaging via Cloud Firestore.
* React 18 operator console.
* Pre-chat forms, offline messages, CSAT ratings.
* File sharing via Firebase Storage.
* Departments and macros (canned responses).
* Display rules, custom appearance, auto-open.
* WooCommerce, WPML, Polylang, Elementor integrations.
* REST API widget config endpoint.
* Identity verification (HMAC-SHA256).
* Premium: License validation, hosted Firebase, FCM push, analytics dashboard.

== Upgrade Notice ==

= 1.0.0 =
Initial release of AdventChat live chat plugin.
