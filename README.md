# AdventChat — Real-Time Live Chat for WordPress

[![WordPress Plugin](https://img.shields.io/badge/WordPress-6.0+-blue)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-8.1+-purple)](https://www.php.net)
[![License: GPLv3](https://img.shields.io/badge/License-GPLv3-green.svg)](https://www.gnu.org/licenses/gpl-3.0.html)
[![GitHub Release](https://img.shields.io/github/v/release/maxymurm/adventchat)](https://github.com/maxymurm/adventchat/releases)
[![Tests](https://github.com/maxymurm/adventchat/workflows/CI/badge.svg)](https://github.com/maxymurm/adventchat/actions)

**Real-time live chat powered by Firebase. Free forever with your own Firebase, or upgrade for hosted infrastructure and a mobile operator app.**

## Features

### Free Tier
- ✨ **Real-time Messaging** — Instant delivery via Cloud Firestore
- 💼 **Operator Console** — React 18 admin dashboard with three-column layout
- 📋 **Pre-chat Forms** — Collect visitor info before chat starts
- 📧 **Offline Messages** — Accept messages when agents are offline
- ⭐ **CSAT Ratings** — Customer satisfaction feedback
- 📁 **File Sharing** — Images and files via Firebase Storage
- ⌨️ **Typing Indicators** — Real-time typing status
- 🔊 **Sound Notifications** — Audio alerts for new messages
- 📄 **Chat Transcripts** — Email transcripts to visitors
- 🏢 **Departments** — Route chats to the right team
- 🚀 **Canned Responses** — Quick reply macros
- 👁️ **Display Rules** — Show/hide by page, post type, role, device
- 🎨 **Custom Styling** — Colors, position, launcher style, custom CSS
- 🔒 **GDPR Consent** — Optional consent checkbox
- 🛒 **WooCommerce** — Cart context, customer identity, order info
- 🌍 **WPML/Polylang** — Full multilingual support
- 🎛️ **Elementor** — Native Elementor widget
- 🔓 **Identity Verification** — HMAC-SHA256 for logged-in users
- 📡 **REST API** — Public widget config endpoint

### Premium Tier
- 🏠 **Hosted Firebase** — One-click provisioning, no Firebase setup
- 📱 **Mobile App** — iOS/Android operator console
- 🔔 **FCM Push** — Push notifications to operator devices
- 📊 **Analytics Dashboard** — Chat volume, ratings, response times
- 🎯 **Priority Support** — Direct support from AdventChat team

## Quick Start

### Installation

1. Download from [WordPress.org Plugin Directory](https://wordpress.org/plugins/adventchat/) or clone from GitHub
2. Upload to `/wp-content/plugins/` and activate
3. Go to **AdventChat → Settings → Firebase**
4. Add your Firebase configuration (or upgrade for hosted tier)
5. The chat widget appears on your site!

### Firebase Setup (Free Tier)

1. Create a project at [Firebase Console](https://console.firebase.google.com/)
2. Enable **Authentication** → **Anonymous Sign-in**
3. Enable **Cloud Firestore** in production mode
4. Copy your web app config (apiKey, projectId, etc.)
5. Paste in AdventChat Settings → Firebase tab

See [Setup Guide](docs/guides/) for detailed instructions.

## Requirements

- **WordPress:** 6.0+
- **PHP:** 8.1+
- **Firebase Project:** Free tier sufficient for most sites

## Documentation

- **[ReadMe](readme.txt)** — Plugin description and FAQ
- **[Setup Guide](docs/guides/)** — Installation and configuration
- **[API Reference](docs/api/)** — REST endpoints, hooks, filters
- **[Architecture](docs/architecture/)** — Technical design overview
- **[Roadmap](docs/ROADMAP.md)** — Future phases and features
- **[Testing Matrix](docs/TESTING_MATRIX.md)** — Compatibility matrix

## Development

### Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 8.1+, WordPress 6.0+ |
| **Real-time** | Cloud Firestore |
| **Auth** | Firebase Anonymous + Email/Password |
| **Widget** | Vanilla JS (zero dependencies) |
| **Console** | React 18 + TypeScript |
| **Mobile** | Ionic 8 + Angular 18 (Capacitor 6) |
| **Tests** | PHPUnit + Jest |
| **CI/CD** | GitHub Actions |

### Building from Source

```bash
# Install dependencies
npm install
composer install

# Build assets
npm run build

# Run tests
npm test              # Jest (widget)
composer test         # PHPUnit (PHP)

# Development watch mode
npm run dev
```

### Project Structure

```
adventchat/
├── adventchat.php           # Main plugin file
├── includes/                # PHP classes
│   ├── class-adventchat.php # Bootstrap
│   ├── admin/               # Settings, lists, analytics
│   ├── api/                 # REST endpoints
│   ├── integrations/        # WooCommerce, WPML, Elementor
│   └── libraries/           # Third-party code
├── assets/
│   ├── js/dist/widget.js    # Visitor chat widget
│   ├── js/dist/console.js   # Operator console (React)
│   └── css/dist/            # Minified styles
├── templates/
│   ├── admin/               # Admin pages
│   └── emails/              # Email templates
├── docs/                    # Technical documentation
├── tests/                   # PHPUnit + Jest test suites
└── languages/               # Translation files
```

## Testing

### PHPUnit (PHP Tests)

```bash
# Run all PHP tests
composer test

# Run specific test class
vendor/bin/phpunit tests/php/SettingsTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Jest (JavaScript Tests)

```bash
# Run all widget tests
npm test

# Watch mode (auto-rerun on changes)
npm run test:watch

# Coverage report
npm test -- --coverage
```

### GitHub Actions CI

Automatic tests run on:
- Push to `main`, `develop`, or `feature/*` branches
- Pull requests to `main` or `develop`

Tests matrix: PHP 8.1/8.2/8.3 × WordPress 6.5/latest

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Write tests for your changes
4. Commit with descriptive messages
5. Push and open a pull request

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## Security

Found a security issue? Please email security@adventchat.com or use the [GitHub Security Advisory](https://github.com/maxymurm/adventchat/security) feature.

See our [Security Policy](SECURITY.md) for more information.

## License

GPLv3 or later. See [LICENSE](LICENSE) for details.

## Support

- **Issues:** [GitHub Issues](https://github.com/maxymurm/adventchat/issues)
- **Discussions:** [GitHub Discussions](https://github.com/maxymurm/adventchat/discussions)
- **Premium Support:** [adventchat.com/support](https://adventchat.com/support)
- **Documentation:** [docs/](docs/)

## Project Status

- **v1.0.0:** ✅ Released (March 2026)
  - All 90 core issues (Phases 1-9) complete
  - PHPUnit + Jest test suites with CI
  - WordPress.org-ready submission

- **Upcoming:** Phase 10 — Mobile Operator App (Ionic 8 + Angular 18)

See [Roadmap](docs/ROADMAP.md) for future phases and features.

---

**Made with ❤️ by [@maxymurm](https://github.com/maxymurm)**
