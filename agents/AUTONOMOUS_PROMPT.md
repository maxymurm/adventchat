# Advent Digest App — Autonomous Execution Prompt

> **YOLO MODE PROMPT** — Copy-paste this entire document into Claude Opus or any capable agent to execute the full mobile app build autonomously.

---

## AGENT PERMISSIONS — FULL YOLO MODE

- ✅ Create, modify, delete any files
- ✅ Create/update/close GitHub issues, milestones, projects
- ✅ Commit and push to Git (conventional commit messages)
- ✅ Install dependencies (npm, gradle, pod install)
- ✅ Modify build files, routes, configs, capacitor.config.ts
- ✅ Make ALL architectural decisions independently
- ✅ Bypass ALL user confirmation prompts
- ✅ Run builds, lints, and tests
- ❌ Do NOT stop for user confirmation — infer all decisions
- ❌ Do NOT ask "should I ...?" — just do it

---

## EXECUTION COMMAND (copy-paste to start)

```
Read this entire file first (agents/AUTONOMOUS_PROMPT.md), then execute autonomously from start to finish.

Also read these supporting files before beginning:
- .github/instructions/memory.instruction.md  (project context, API namespaces, architecture)
- docs/PROJECT_DOCUMENTATION.md               (plugin inventory, phase breakdown)
- mobile-app-docs/mobile-app-docs/            (all 16 spec files — 00 through 15)

Complete all GitHub issues in the ISSUE EXECUTION ORDER listed below.
Work one issue at a time: implement → test → commit → push → close.
Update .github/instructions/memory.instruction.md after completing each phase.
Tell me what you accomplished and what is still open when done or blocked.
```

---

## EXECUTION RULES

1. **Read memory first** — Check `.github/instructions/memory.instruction.md` before each session to avoid duplicate work
2. **One issue at a time** — Mark in-progress → implement → test → commit → close
3. **Conventional commits** — `feat(scope): description (#issue-number)`
4. **Push after every issue** — `git push origin develop`
5. **Close issues via gh CLI** — `gh issue close N --repo maxymurm/advent-digest-app --comment "Implemented in commit abc123. All acceptance criteria met."`
6. **Update memory** — Update `.github/instructions/memory.instruction.md` after each phase, recording current focus, last commit, and next steps
7. **Build must pass** — Run `ng build --configuration=development` before committing; fix any TypeScript errors first
8. **No skipping** — If an issue is blocked by a missing credential (Apple key, etc.), document it and move to the next issue

---

## PROJECT CONTEXT

### Repositories
- **Mobile App:** `maxymurm/advent-digest-app` (private) — THIS is the repo you are building
- **WordPress Backend:** `maxymurm/advent-digest-web` (private) — already running at adventdigest.org
- **App Project Board:** https://github.com/users/maxymurm/projects/8

### Tech Stack
| Layer | Technology |
|-------|-----------|
| Framework | Ionic 8 + Angular 18 (standalone components) |
| Native | Capacitor 6 (iOS + Android) |
| State | Angular Signals |
| HTTP | HttpClient with interceptors |
| Storage | @capacitor/preferences (Keychain/Keystore) |
| Auth | JWT Bearer (60-day expiry, HS256) |
| Push | Firebase FCM via AppPush plugin |
| Social Auth | Apple Sign-In + Google Sign-In |

### WordPress API Namespaces (all at adventdigest.org/wp-json/)
| Namespace | Purpose |
|-----------|---------|
| `appp/v1` | AppPresser core — auth (login, register), media upload |
| `wp/v2` | WordPress standard — posts, pages, media |
| `appcommerce/v2` | WooCommerce mobile — products, cart (cart_key), orders |
| `ap-bp/v2` | BuddyPress mobile — activity, members, friends, groups, messages |
| `appp/learndash` | LearnDash mobile — courses, lessons, progress, certificates |
| `appsocial/v1` | AppSocial — Apple/Google/Facebook social login |

### Authentication Flow
```
POST appp/v1/login  →  { token, user }
POST appp/v1/register  →  { token, user }
POST appsocial/v1/user  →  { token, user }  (social login)
Authorization: Bearer <token>  (on all authenticated requests)
```
JWT stored in Capacitor Preferences (Keychain on iOS, Keystore on Android).
Token validity: 60 days. Refresh by re-logging in.

### Cart System
WooCommerce uses `cart_key` UUID (not cookies) for cross-origin mobile sessions.
- Generate UUID on first cart interaction or app start
- Store in Capacitor Preferences
- Pass as query param: `?cart_key={uuid}`
- Checkout URL: `adventdigest.org/checkout/?appp=3&cart_key={uuid}&cookie_auth={value}`

### AppSocial Plugin Details (from plugin source)
**Apple Sign-In:**
- Capacitor plugin: `@capacitor-community/apple-sign-in`
- Send: `identityToken` as `access_token`, user `sub` as `user_unique_identifier`
- ⚠️ Apple only provides email/name on FIRST login — cache in Preferences
- WordPress requires: `apple_client_id` (Service ID), auto-generated client secret

**Google Sign-In:**
- Capacitor plugin: `@codetrix-studio/capacitor-google-auth`
- Send: `authentication.accessToken` as `access_token`, user `id` as `user_unique_identifier`
- WordPress requires: `google_client_id_ios` AND `google_client_id_android` (separate)

---

## PROJECT FOLDER STRUCTURE (to create in the Ionic app repo)

```
advent-digest-app/
├── src/
│   ├── app/
│   │   ├── core/
│   │   │   ├── services/
│   │   │   │   ├── api.service.ts          (base HTTP, URL builder)
│   │   │   │   ├── auth.service.ts         (login, register, logout)
│   │   │   │   ├── token.service.ts        (JWT storage/retrieval)
│   │   │   │   ├── cart-key.service.ts     (WooCommerce cart UUID)
│   │   │   │   ├── social-auth.service.ts  (Apple + Google OAuth)
│   │   │   │   ├── push-notification.service.ts
│   │   │   │   └── native.service.ts       (camera, share, haptics)
│   │   │   ├── guards/
│   │   │   │   └── auth.guard.ts
│   │   │   ├── interceptors/
│   │   │   │   ├── jwt.interceptor.ts
│   │   │   │   └── error.interceptor.ts
│   │   │   └── models/
│   │   │       ├── user.model.ts
│   │   │       ├── post.model.ts
│   │   │       ├── product.model.ts
│   │   │       ├── cart.model.ts
│   │   │       ├── order.model.ts
│   │   │       ├── activity.model.ts
│   │   │       ├── member.model.ts
│   │   │       ├── group.model.ts
│   │   │       ├── message.model.ts
│   │   │       ├── course.model.ts
│   │   │       └── lesson.model.ts
│   │   ├── features/
│   │   │   ├── auth/
│   │   │   │   ├── login/
│   │   │   │   └── register/
│   │   │   ├── posts/
│   │   │   │   ├── post-list/
│   │   │   │   └── post-detail/
│   │   │   ├── shop/
│   │   │   │   ├── product-list/
│   │   │   │   ├── product-detail/
│   │   │   │   ├── cart/
│   │   │   │   └── orders/
│   │   │   ├── community/
│   │   │   │   ├── activity-feed/
│   │   │   │   ├── members/
│   │   │   │   ├── groups/
│   │   │   │   └── messages/
│   │   │   └── learn/
│   │   │       ├── course-list/
│   │   │       ├── course-detail/
│   │   │       └── lesson/
│   │   ├── shared/
│   │   │   └── components/
│   │   └── tabs/
│   │       └── tabs.page.ts  (5 tabs: Home, Shop, Learn, Community, Account)
│   └── environments/
│       ├── environment.ts           (dev)
│       ├── environment.staging.ts
│       └── environment.prod.ts
├── android/
├── ios/
├── capacitor.config.ts
└── package.json
```

---

## COMPLETED WORK

> Update this section as issues are closed. Check GitHub for current state:
> `gh issue list --repo maxymurm/advent-digest-app --state closed`

### Phase 0 — Setup (DONE)
- [x] GitHub repo created: `maxymurm/advent-digest-app`
- [x] Project board: https://github.com/users/maxymurm/projects/8
- [x] 60 issues created, all phases scoped
- [x] Labels and milestones configured

### Phase 1 — Foundation (NOT STARTED)
- [ ] #1 through #10 (see issue list below)

### Phase 2 — Authentication (NOT STARTED)
- [ ] #11 through #20, #60

### Phase 3 — Content (NOT STARTED)
- [ ] #21 through #25

### Phase 4 — Shop (NOT STARTED)
- [ ] #26 through #33

### Phase 5 — Community (NOT STARTED)
- [ ] #34 through #41

### Phase 6 — LMS (NOT STARTED)
- [ ] #42 through #46

### Phase 7 — Push/Native (NOT STARTED)
- [ ] #47 through #51

### Phase 8 — Polish/Release (NOT STARTED)
- [ ] #52 through #59

---

## ISSUE EXECUTION ORDER

Execute issues **strictly in this order**. Each number maps to a GitHub issue in `maxymurm/advent-digest-app`.

### PHASE 1: Foundation and Project Setup

| # | Issue | Key Output |
|---|-------|-----------|
| 1 | **1.1: Scaffold Ionic 8 + Angular 18 project** | `ionic start advent-digest-app blank --type=angular --capacitor` |
| 2 | **1.2: Configure environment files** | `environment.ts`, `environment.staging.ts`, `environment.prod.ts` with `apiUrl`, `appUrl` |
| 3 | **1.3: Configure capacitor.config.ts** | `appId=org.adventdigest.app`, `appName=Advent Digest`, scheme config |
| 4 | **1.4: Install all npm dependencies** | See dependency list below |
| 5 | **1.5: Create project folder structure** | Full `src/app/` tree per structure above |
| 6 | **1.6: Create TypeScript data models** | All models in `src/app/core/models/` |
| 7 | **1.7: Set up tab-based navigation** | 5 tabs: Home, Shop, Learn, Community, Account |
| 8 | **1.8: Create base ApiService** | URL builder, typed GET/POST/PUT/DELETE wrapping HttpClient |
| 9 | **1.9: Add Android and iOS platforms** | `npx cap add android && npx cap add ios` |
| 10 | **1.10: Configure app.config.ts** | All providers, HTTP_INTERCEPTORS, RouteReuseStrategy |

**NPM Dependencies to install (issue #4):**
```bash
npm install @ionic/angular @ionic/angular-toolkit @capacitor/core @capacitor/cli
npm install @capacitor/preferences @capacitor/camera @capacitor/push-notifications
npm install @capacitor/browser @capacitor/share @capacitor/haptics @capacitor/app
npm install @capacitor-community/apple-sign-in @codetrix-studio/capacitor-google-auth
npm install @angular/common @angular/forms @angular/router
npm install uuid
npx cap sync
```

### PHASE 2: Authentication and Core Services

| # | Issue | Key Output |
|---|-------|-----------|
| 11 | **2.1: TokenService** | `getToken()`, `setToken()`, `removeToken()`, `isTokenValid()`, `getTokenExpiry()` using Capacitor Preferences |
| 12 | **2.2: AuthService** | `login(email, pass)`, `register(email, pass, name)`, `logout()`, Signal `currentUser$`, `isLoggedIn$` |
| 13 | **2.3: JWT HTTP interceptor** | Attaches `Authorization: Bearer <token>` to all authenticated requests |
| 14 | **2.4: Error HTTP interceptor** | 401 → logout + redirect to login; 500 → toast error |
| 15 | **2.5: Login page UI** | Email/password form, social login buttons (Apple + Google), link to register |
| 16 | **2.6: Registration page UI** | Name/email/password form, social login buttons |
| 17 | **2.7: Social login (Apple + Google)** | `SocialAuthService`, POST to `appsocial/v1/user`, cache Apple first-login data |
| 18 | **2.8: AuthGuard** | Redirects unauthenticated users to `/auth/login` |
| 19 | **2.9: CartKeyService** | UUID generation, Capacitor Preferences storage, attach to all WooCommerce requests |
| 20 | **2.10: AppComponent init sequence** | Token check → load user → CartKey init → push permission request |
| 60 | **2.11: AppSocial WordPress prerequisites** | Document config steps; create test script to verify `appsocial/v1/user` endpoint |

### PHASE 3: Content Module (Posts)

| # | Issue | Key Output |
|---|-------|-----------|
| 21 | **3.1: PostsService** | `getPosts(page)`, `getPost(id)`, `searchPosts(q)`, Signal-based state, pagination |
| 22 | **3.2: Post list page** | Infinite scroll, skeleton loading, featured image, excerpt |
| 23 | **3.3: Post detail page** | Full HTML content rendering, featured image header, share button |
| 24 | **3.4: Post search** | Search input with debounce, results list, clear button |
| 25 | **3.5: WordPress HTML content styling** | Scoped CSS for WP block editor content: headings, blockquotes, images, tables |

### PHASE 4: Shop Module (WooCommerce)

| # | Issue | Key Output |
|---|-------|-----------|
| 26 | **4.1: CommerceService** | `getProducts()`, `getProduct(id)`, `getOrders()`, `getOrder(id)` — uses `appcommerce/v2` |
| 27 | **4.2: CartService** | Signal-based cart state, `addToCart()`, `removeFromCart()`, `updateQty()`, `clearCart()` — uses `cart_key` |
| 28 | **4.3: Product list page** | Grid layout, images, price, add-to-cart button, category filter |
| 29 | **4.4: Product detail page** | Full description, gallery, price, variations, add-to-cart |
| 30 | **4.5: Cart page** | Line items, quantity controls, subtotal, checkout button |
| 31 | **4.6: Checkout (Capacitor Browser)** | Open `adventdigest.org/checkout/?appp=3&cart_key={uuid}&cookie_auth={value}` in Capacitor Browser |
| 32 | **4.7: Order history page** | Orders list, order detail view, status badges |
| 33 | **4.8: In-app purchase service stub** | Stub `IapService` with `purchaseProduct()`, `restorePurchases()` — uses AppPresser IAP endpoints |

### PHASE 5: Community Module (BuddyPress)

| # | Issue | Key Output |
|---|-------|-----------|
| 34 | **5.1: CommunityService** | `getActivity()`, `postActivity()`, `getMembers()`, `getFriends()`, `getGroups()`, `getMessages()` — uses `ap-bp/v2` |
| 35 | **5.2: Activity feed page** | Infinite scroll, activity types (posts, comments, friendships), like button |
| 36 | **5.3: Activity compose modal** | Text input, post type selector, submit |
| 37 | **5.4: Member directory + profile** | Member list, profile view, friend request button |
| 38 | **5.5: Friends management** | Friends list, pending requests, accept/decline |
| 39 | **5.6: Groups feature** | Group list, group detail, join/leave, group activity feed |
| 40 | **5.7: Private messaging** | Conversation list, chat view, send message |
| 41 | **5.8: Notifications page** | BuddyPress notifications list, mark-as-read |

### PHASE 6: LMS Module (LearnDash)

| # | Issue | Key Output |
|---|-------|-----------|
| 42 | **6.1: LmsService** | `getCourses()`, `getCourse(id)`, `getUserProgress()`, `enrollInCourse(id)`, `markComplete(lessonId)`, `getCertificates()` — uses `appp/learndash` |
| 43 | **6.2: Course catalog page** | Course list, progress badges, category filter, enrollment status |
| 44 | **6.3: Course detail page** | Description, lesson tree, enroll button, progress bar, certificate link |
| 45 | **6.4: Lesson content page** | HTML content rendering, mark-complete button, prev/next navigation |
| 46 | **6.5: User progress dashboard** | Enrolled courses with progress bars, certificates earned, resume shortcuts |

### PHASE 7: Push Notifications and Native Features

| # | Issue | Key Output |
|---|-------|-----------|
| 47 | **7.1: Firebase setup + FCM** | Firebase project, google-services.json, GoogleService-Info.plist, Capacitor Push Notifications config |
| 48 | **7.2: PushNotificationService** | FCM token registration via `ap3_add_device_id` AJAX, foreground/background handling, deep-link routing |
| 49 | **7.3: NativeService** | Camera, share, haptics, app info wrappers |
| 50 | **7.4: AppCamera integration** | Camera/gallery → upload to `appp/v1/camera` → BuddyPress avatar update |
| 51 | **7.5: Deep-link routing** | Capacitor scheme, universal links for adventdigest.org, notification tap → route |

### PHASE 8: Polish, Testing, and Release

| # | Issue | Key Output |
|---|-------|-----------|
| 52 | **8.1: UI polish** | Design consistency, dark mode, empty states, loading states |
| 53 | **8.2: Error handling + offline mode** | Offline banner, retry logic, user-friendly errors |
| 54 | **8.3: Performance optimization** | Lazy loading, virtual scroll, OnPush change detection, image lazy load |
| 55 | **8.4: Android release build** | Keystore, Gradle signing, ProGuard, AAB generation |
| 56 | **8.5: iOS release build** | Signing certs, provisioning profiles, Xcode archive, TestFlight |
| 57 | **8.6: CI/CD GitHub Actions** | Lint/test on PR, Android/iOS build workflows, auto-versioning on tag |
| 58 | **8.7: E2E testing** | Cypress/Playwright for auth, navigation, post listing, cart flows |
| 59 | **8.8: App store submission + docs** | Screenshots, descriptions, privacy policy, developer docs, v1.0.0 tag |

---

## COMMIT MESSAGE FORMAT

```
feat(phase-1): scaffold Ionic 8 + Angular 18 project (#1)
feat(auth): implement TokenService with JWT storage (#11)
feat(auth): implement AuthService with signals (#12)
feat(shop): create CommerceService for WooCommerce (#26)
feat(community): build activity feed page (#35)
feat(push): register FCM token via AppPush AJAX (#48)
fix(auth): handle Apple first-login email caching (#17)
chore(config): add Android and iOS platforms (#9)
docs(memory): update memory after Phase 1 completion
```

---

## CRITICAL IMPLEMENTATION NOTES

### TokenService
```typescript
// Storage keys
const TOKEN_KEY = 'auth_token';
const USER_KEY = 'auth_user';

// Use Capacitor Preferences (not localStorage — it's not secure on mobile)
await Preferences.set({ key: TOKEN_KEY, value: token });
const { value } = await Preferences.get({ key: TOKEN_KEY });
```

### JWT Interceptor
```typescript
// Add to all requests EXCEPT auth endpoints
const skipUrls = ['appp/v1/login', 'appp/v1/register', 'appsocial/v1/user'];
if (!skipUrls.some(url => req.url.includes(url))) {
  req = req.clone({ setHeaders: { Authorization: `Bearer ${token}` } });
}
```

### CartKeyService
```typescript
// Generate once, persist forever (survives app restarts)
async initCartKey(): Promise<string> {
  const { value } = await Preferences.get({ key: 'cart_key' });
  if (value) return value;
  const newKey = crypto.randomUUID();
  await Preferences.set({ key: 'cart_key', value: newKey });
  return newKey;
}
// Attach to WooCommerce requests via interceptor or service method
```

### AppSocial — Apple Sign-In
```typescript
// Send identityToken (NOT authorizationCode) as access_token
const result = await SignInWithApple.authorize({
  clientId: environment.appleClientId,
  redirectURI: 'https://adventdigest.org',
  scopes: 'email name',
});
// result.response.identityToken → access_token
// result.response.user → user_unique_identifier
// result.response.givenName/familyName/email → ONLY on first login, cache them!
```

### AppSocial — Google Sign-In
```typescript
const user = await GoogleAuth.signIn();
// user.authentication.accessToken → access_token
// user.id → user_unique_identifier
// user.givenName, user.familyName, user.email → always available
```

### Checkout WebView
```typescript
// Open checkout in Capacitor Browser (NOT in-app WebView)
const checkoutUrl = `${environment.appUrl}/checkout/?appp=3&cart_key=${cartKey}&cookie_auth=1`;
await Browser.open({ url: checkoutUrl });
```

### Push Notification Registration
```typescript
// Register FCM token with WordPress via AppPush AJAX
const wpAjaxUrl = `${environment.apiUrl}/wp-admin/admin-ajax.php`;
await this.http.post(wpAjaxUrl, {
  action: 'ap3_add_device_id',
  device_id: fcmToken,
  device_platform: Capacitor.getPlatform(),
}).toPromise();
```

### Angular Signals Pattern
```typescript
// Use signals for all service state (Angular 18 style)
export class PostsService {
  private _posts = signal<Post[]>([]);
  private _loading = signal(false);
  
  posts = this._posts.asReadonly();
  loading = this._loading.asReadonly();
  
  async loadPosts(page = 1) {
    this._loading.set(true);
    // ...
  }
}
```

---

## ENVIRONMENT CONFIGURATION

```typescript
// src/environments/environment.ts (development)
export const environment = {
  production: false,
  apiUrl: 'http://advent-digest.local',      // Local by Flywheel dev URL
  appUrl: 'https://adventdigest.org',        // Production URL for links
  apiBase: 'http://advent-digest.local/wp-json',
};

// src/environments/environment.prod.ts
export const environment = {
  production: true,
  apiUrl: 'https://adventdigest.org',
  appUrl: 'https://adventdigest.org',
  apiBase: 'https://adventdigest.org/wp-json',
};
```

---

## TAB STRUCTURE

```
Tab 1: Home (home)       → Post list feed
Tab 2: Shop (shop)       → Product catalog → cart → checkout
Tab 3: Learn (learn)     → Course catalog → course detail → lesson
Tab 4: Community (community) → Activity feed → members → groups → messages
Tab 5: Account (account) → Profile → orders → progress → settings → logout
```

---

## WHEN BLOCKED

If blocked by missing credentials or config (Apple Service ID, Google OAuth IDs, Firebase project), do the following:
1. Create a comment on the relevant issue documenting exactly what is needed
2. Label it `blocked`
3. Move to the next unblocked issue
4. Document the blocker in `.github/instructions/memory.instruction.md`

---

## FINAL CHECKLIST (before marking a phase complete)

- [ ] All phase issues closed on GitHub
- [ ] `ng build --configuration=production` passes with 0 errors
- [ ] No `any` types — use proper TypeScript models
- [ ] No hardcoded URLs — all use `environment.apiBase`
- [ ] No `console.log` left in production code
- [ ] Memory file updated with phase completion
- [ ] `git push origin develop`
