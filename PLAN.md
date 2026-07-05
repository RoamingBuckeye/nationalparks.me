# nationalparks.me — Plan

## Software Stack

Single Laravel codebase, dual-target: the **same project** is served as a web app and packaged into native iOS/Android shells via NativePHP for Mobile. Where the two targets diverge (database, storage, sync), behavior is switched via config and a runtime context flag.

### Backend (web + mobile-embedded PHP)

| Component | Version | Notes |
|---|---|---|
| PHP | 8.5.3 (host) / 8.5.7 (embedded in mobile builds) | Mobile apps ship a statically-compiled PHP runtime |
| Laravel | 13.17 | App framework |
| Inertia.js (server adapter) | 3.x | `inertiajs/inertia-laravel` |
| Authentication | Fortify-based starter kit | Email/password, passkeys, two-factor — same flow web + mobile |
| Queue / jobs | Laravel Horizon 5.47 | Redis-backed; web only (mobile has no long-lived workers) |

### Frontend (Vue + Inertia, served to web browser and mobile WebView)

| Component | Version | Notes |
|---|---|---|
| Vue | 3.5 | Composition API, TypeScript |
| Inertia.js (client) | 3.x | `@inertiajs/vue3` |
| Vite | latest | `laravel-vite-plugin`, `@vitejs/plugin-vue`, `@inertiajs/vite`, `@laravel/vite-plugin-wayfinder` |
| Wayfinder | — | Typed route helpers for the frontend |
| Tailwind CSS | 4 | via `@tailwindcss/vite`; passport-theme tokens in `resources/css/app.css` |
| Fonts | Instrument Sans + Fraunces | body sans + serif display; self-hosted via the Vite/Bunny pipeline |
| TypeScript | strict | starter kit defaults |
| axios | latest | re-added; Inertia 3 dropped it but NativePHP needs it |
| Testing | Vitest + Vue Test Utils (frontend); PHPUnit/Pest (backend); Playwright (E2E) | all configured and CI-gated |

### Mobile (NativePHP for Mobile v3)

| Component | Version | Notes |
|---|---|---|
| NativePHP Mobile | 3.3.6 | MIT-licensed core; Composer package |
| App identifier | `me.nationalparks.app` | `NATIVEPHP_APP_ID` in `.env` |
| iOS shell | Xcode 26.5 detected | `nativephp/ios/` (generated, gitignored) |
| Android shell | Android Studio 2025.3.4 detected | `nativephp/android/` (generated, gitignored) |
| Build-time prerequisites (not yet installed) | CocoaPods, JDK/Gradle (via Android Studio bundle) | required before first device build |
| Distribution | Apple Developer Program ($99/yr), Google Play Console ($25 one-time) | required for store submission |
| CI/CD | Bifrost (optional, deferred) | $29–$129/mo if/when we want cloud builds + OTA updates |
| Premium plugins (deferred) | NativePHP Starter Kit bundle, $199 one-time | Biometrics, Geolocation, Push (Firebase), Scanner, Secure Storage |

### Local development environment (Laravel Sail / Docker)

| Service | Image | Port | Purpose |
|---|---|---|---|
| `laravel.test` | `sail-8.5/app` | 80, 5173 (Vite HMR) | App + Vite dev server |
| `mysql` | `mysql:8.4` | 3306 | Primary database (web) |
| `redis` | `redis:alpine` | 6379 | Cache + queue (Horizon) |
| `mailpit` | `axllent/mailpit` | 1025 (SMTP), 8025 (UI) | Local email capture |

### Dual-target architecture (how web and mobile share one codebase)

| Concern | Web target | Mobile target |
|---|---|---|
| Where PHP runs | Sail/Docker container (dev) → cloud server (prod) | On-device, bundled with the app |
| Database | MySQL | Local SQLite (bundled), syncs to web API |
| File storage | Server disk / S3 | Device sandbox |
| Auth | Sessions + cookies | Sanctum tokens against the web API |
| Background jobs | Horizon (Redis) | Not available — defer to web |
| Network | Always assumed | Offline-first; sync when online |

Switching is driven by environment/config (e.g. a `RUNTIME_TARGET` flag + dedicated `config/database.php` connection per target). Routes, controllers, models, Inertia pages, and Vue components are shared.

### Tooling on the host

| Tool | Version | Notes |
|---|---|---|
| Composer | 2.9.5 | |
| Node | 25.6.1 | |
| npm | 11.11.0 | |
| Docker | 29.2.1 | Sail orchestration |
| Xcode | 26.5 | iOS builds |
| Android Studio | 2025.3.4 | Android builds |
| Laravel installer | 5.28.1 (global) | `~/.composer/vendor/bin/laravel` |

### Open items in the stack

- Install CocoaPods (`brew install cocoapods`) before first iOS device build
- Confirm Java/Gradle pickup once we open the Android project in Android Studio once
- ~~NPS Data API key~~ — **resolved**: key located and in `.env`; all entities synced (63 parks, ~7.5k POIs, 169 alerts)
- Apple Developer Program enrollment — required when we're ready to test on a real iPhone

## Functionality

### Implementation status

**Web app: feature-complete for the core scope, plus a collectible Stamps feature** (as of 2026-07-04). Test coverage: **206 Pest** (backend), **12 Vitest** (Vue components), and a **Playwright E2E** journey. Pint, PHPStan level 7, ESLint, vue-tsc, Prettier all clean; enforced by a pre-push hook (the E2E suite runs in its own CI workflow, not the hook). Shipped slices:

- **Auth** — Fortify email/password, required email verification, TOTP + hand-rolled email-code 2FA, passkeys, honeypot on registration. `display_name` + `share_enabled` surfaced.
- **Mobile token API** — Sanctum bearer-token endpoints (`POST /api/login`, `/api/two-factor-challenge`, `/api/two-factor-challenge/email-code`, `/api/logout`, `GET /api/user`) with full 2FA parity (TOTP, email code, recovery codes) and the same email-verification gate as the web. Tokens never expire.
- **Closure indicators** — red "Closure" chip on park cards (list + shared page) and a red ring on map pins for parks with an active NPS "Park Closure" alert.
- **NPS data** — 63 parks, ~7.5k POIs, and 169 alerts synced and refreshed on schedule.
- **Core visit loop** — browse parks (list + search/state/visited filters), park detail, log live/past visits with a Journal, per-visit POI check-off (paginated checklist).
- **Photos** — multi-file visit photo upload with EXIF capture and server-side thumbnails, served through an authorized streaming route; uploads are transactional (no orphaned files on partial failure).
- **Map** — Leaflet + OpenStreetMap map of all parks by visited state (authenticated `/map` + the public shared page).
- **Sharing** — token-gated, read-only public list + map; generate / rotate / revoke from settings.
- **Alerts** — NPS alerts on park detail as a compact, severity-ordered two-level accordion.
- **Dashboard + branded homepage** — real stats; the **passport design system** (paper & pine palette, Fraunces serif display, mono for data) lives in `resources/css/app.css`.
- **Stamps (collectible)** — earn stamps by checking into parks. 45 seeded: 5 count milestones, 32 state/territory collections, 8 NPS Passport regions. A `/stamps` page grouped by tier with live progress, and a celebratory reveal modal on check-in. See the **Stamps** section below.

**Remaining / deferred:**

- **Mobile track (largest unbuilt area):** the NativePHP build and offline-first SQLite sync. The Sanctum token API the app will authenticate against is now built (see Mobile token API above).
- **Styling refactor (in progress):** moving off Tailwind utility styling to a **custom CSS system** — **BEM** (Block-Element-Modifier) class naming + **Atomic Design** component organization. Scope (locked): **full Tailwind removal**, rewriting every app component and every `ui/` primitive; hand-written BEM lives in a **central `resources/css/` tree** (`atoms/`, `molecules/`, `organisms/`) imported into `app.css`, decoupled from the SFCs. The passport palette/type tokens carry over unchanged as CSS custom properties (only the class layer changes). Migrating incrementally — Tailwind stays loaded until the last component is converted, then it's removed.
  - **Done — the entire live `ui/` primitive library is off Tailwind:** Stamp (molecule pilot, PR #31); simple atoms — AppLogo, Heading, InputError, TextLink (#32); `ui/` primitives — Button/Card/Input/Label (#33), Badge/Avatar/Separator/Spinner/Skeleton/Checkbox (#34), Dialog/Sheet (#35), DropdownMenu/NavigationMenu (#36), Sonner/InputOTP (#38), Alert/Breadcrumb (#39). Collapsible needed no change (class-free reka wrappers). The `ui/` primitives keep their `cva`/`cn` structure but emit BEM classes; `cva` variants → BEM modifiers, reka `data-*` attributes → attribute selectors, `tw-animate-css` → local `np-*` keyframes. (#38 was wrongly called "complete"; a grep audit caught alert + breadcrumb still on Tailwind → fixed in #39. Lesson: audit, don't assume.)
  - **Remaining:** app molecules/organisms (AppHeader, ParkAlerts, ParksMap, UserMenuContent, UserInfo, AppearanceTabs, DeleteUser, Passkey/2FA components, forms) → pages + layouts (Dashboard, parks/*, visits/*, stamps/*, Map, shared/*, Welcome, auth/*, settings/* — the layout-Tailwind-heavy bulk) → **delete dead code** (`select`/`sidebar`/`tooltip` ui dirs + the unused sidebar-variant branches in AppShell/AppContent) → **final Tailwind teardown** (remove `@tailwindcss/vite`, the `@theme` block, `tw-animate-css`, `prettier-plugin-tailwindcss`; move tokens into a `settings/` layer; add a `.sr-only` utility; sweep leftover `size-4` on icons in converted `ui/` files).
  - **Conventions (reference for continuing):** flat BEM (`.block__element--modifier`), colors/fonts via `var(--color-*)`/`var(--font-*)`, one stylesheet per component under the atomic tree, `ui/` files stay in `components/ui/` (prettier/eslint-ignored — keep shadcn style), app components move into `components/{atoms,molecules,organisms}/`. Each batch: convert → gates (Pint/PHPStan not needed; ESLint, vue-tsc, Vitest, `vite build`) → visual check → E2E → one focused PR → merge.

Per-section detail and the decisions log follow below.

### Users & authentication

**Stack:** Laravel **Fortify** for auth flows + **Sanctum** for mobile API tokens + **`laravel/passkeys`** for WebAuthn + **`spatie/laravel-honeypot`** for bot deterrence on the registration form. All Vue/Inertia pages come from the Vue Starter Kit and are extended in place.

- **Sign-up:** email + password + optional `display_name`. No social logins.
- **Email verification:** **required** — account stays pending until verified (enforced via `MustVerifyEmail` on `User` + Fortify's verify flow + middleware-guarded routes).
- **MFA (optional, per user):**
  - TOTP via authenticator app (Google Authenticator, 1Password, etc.)
  - Email-code as alternate second factor (hand-rolled — no off-the-shelf package; ~50 LOC: Action + Mailable + Form Request + a small storage column)
  - **Passkeys (WebAuthn)** via `laravel/passkeys` — optional, never required
- **Profile / Settings:** `display_name`, `share_enabled` toggle, share URL with rotate/revoke, password change, 2FA setup, passkey enrollment, account deletion.
- **Honeypot:** Spatie Laravel Honeypot on the registration form (no UI surface).

### Core actions (authenticated)

**Status (2026-06-29):** the web core visit loop is built — park list (`/parks`, visited state + search/state/visited filters), park detail (`/parks/{park}`, POI kind counts + your visits + check-in / log-past-visit), and the visit page (`/visits/{visit}`, editable dates + Journal, live/end toggle, paginated POI checklist with per-visit check-off). Dashboard shows real stats. **Visit-level photo upload is built**: multi-file upload on the visit page, EXIF `taken_at`/GPS captured on ingest, stored on the default disk (local in dev), served through an authorized `/photos/{photo}` route (S3-ready), with delete. On upload a 500×500 cover-cropped JPEG **thumbnail** is generated (Intervention Image, `photos.thumbnail_path`) and served to the gallery via `?variant=thumbnail` (≈90% smaller; full image on click-through; falls back to the original when no thumbnail exists). Photos are stored polymorphically (`photoable`), attached at the visit level.

1. **Log a park visit**
   - Check in **live** (now) or **log a past visit** (backdated)
   - Visit captures: park, date(s), optional notes, optional photos
2. **Check off Points of Interest (POIs) within a park**
   - Each park has a curated list of POIs (trails, peaks, visitor centers, viewpoints, etc.)
   - Per-visit, the user ticks off which POIs they experienced
   - Notes and photos may attach to a POI individually as well as to the visit overall
3. **Notes & photos**
   - Free-text notes on a visit and on each POI
   - Photo uploads attached to visit or POI

### Visualization

- **List view:** all 63 National Parks with visited/unvisited state, per-park visit count, last-visited date
- **Map view:** US map with parks pinned, color/state encoded as visited vs. unvisited; tap/click a pin to see visit summary
- Filtering / sorting: by state, by visited status, by date of last visit

**Status (2026-06-29):** built with **Leaflet + OpenStreetMap** (free, no API key). A reusable `ParksMap` Vue component (OSM tiles, `circleMarker`s colored green=visited / gray=unvisited, click popups with visit summary) powers both an authenticated `/map` page (sidebar nav, links pins to park detail) and the public shared page. `MapController` returns parks with coordinates + the user's visited state via `Park::withVisitStatsFor()`.

### Sharing

**Status (2026-06-29):** built. Token management lives on a dedicated **Sharing settings page** (`/settings/sharing`): generate / rotate / revoke + copy URL. The `share_enabled` master toggle stays on the Profile page (per the locked decision); the Sharing page reflects its state and links to Profile when off. The public page (`/s/{token}`) is a standalone, read-only list of all 63 parks with the owner's visited state — gated to 404 unless the token is active **and** `share_enabled` is on (never reveals a token exists). The byline shows `display_name`, falling back to a generic "A National Parks explorer" so the real `name` is never exposed. The shared page now renders the Leaflet map (visited/unvisited pins) above the list.

- A user can generate a **public share link** for their list and/or map
- The shared page is read-only and doesn't reveal account/profile data beyond a display name
- The link is revocable; the user can rotate/disable it from their settings

### Alerts

**Status (2026-06-29):** built (park detail). The synced NPS alerts are surfaced on `/parks/{park}` via a reusable `ParkAlerts.vue` component — active alerts only, severity-ordered (Danger → Park Closure → Caution → Information). Rendered as a **two-level accordion, collapsed/compact by default**: the section collapses to a single row whose header still shows severity count chips (e.g. "6 Closures · 3 Cautions · 1 Info") so a collapsed section never hides a closure; expanding reveals one-line per-alert rows that each expand to show description + NPS link. `AlertCategory::severity()` drives ordering; `Park::alerts()` + `Alert::scopeActive()` back the query. **Closure indicators (built 2026-06-30):** parks with an active `Park Closure` alert get a red "Closure" chip on the list + shared page and a red ring on their map pin. A `Park::scopeWithClosureStatus()` subquery feeds a `closed` flag through `SummarizePark`, so all three pages share one source of truth.

### Stamps

**Status (2026-07-04):** built. Collectible "stamps" (the UI term everywhere) earned by checking into parks, in the spirit of the NPS Passport program and Gowalla's location badges.

- **Earning:** a check-in (a `visits` row) is the signal; distinct parks are counted. The `EvaluateStamps` action runs on check-in (and via a `stamps:evaluate` backfill command), is idempotent, and earning is **sticky** (never revoked). Progress is computed live, never stored.
- **Three criteria types** (`StampCriteria` enum): `park_count` (any N distinct parks), `state_set` (all parks in a state), `region_set` (all parks in a Passport region). State/region membership is **derived from `parks.states`** (`whereJsonContains`), so no hand-maintained park lists.
- **Catalog (45, seeded via `StampSeeder`):** 5 milestones (1/5/10/25/63), 32 state/territory collections with special names (Utah = "Mighty Five", Ohio = "Buckeye", …), and the 8 usable NPS **Passport regions**, each in its official passport color. Slugs are code-based (`state-ut`, `region-southeast`) so the first-draft names can be revised without breaking earned identity.
- **Versioning:** `stamps.members_changed_at` vs `user_stamps.earned_at` flags "vintage" editions — if a collection's set later changes, earlier earners keep the stamp shown with its year, e.g. "Buckeye · 2025".
- **UI:** a reusable `<Stamp>` badge (SVG scene + accent color, greyscale when locked — placeholder scene art for now); a `/stamps` page grouped by tier with earned/locked + live progress; a global `StampReveal` modal that fires on check-in from the `flash.stampsEarned` payload.
- **Tables:** `stamps` (definitions) and `user_stamps` (earned; unique `(user_id, stamp_id)`, `earned_at`; row exists ⇔ earned).

### Out of scope (for now)

- Social logins
- Social features beyond a share link (no follows, comments, feeds)
- In-app purchases / paid tiers
- Offline POI editing on mobile (sync model handles offline reads; offline writes are a phase 2 consideration)

## NPS API — what we'll ingest

API base: `https://developer.nps.gov/api/v1/` · auth: `?api_key=...` or `X-Api-Key` header · rate limit: 1,000 req/hr.

### Relevant endpoints and their shape (probed live with DEMO_KEY against Yellowstone)

| Endpoint | What it is | Rough volume per park | Use for |
|---|---|---|---|
| `/parks` | Park profile | 1 per park (63 total) | `parks` table |
| `/places` | Curated scenic/historic spots | ~500 (Yellowstone) | POI table — primary source |
| `/thingstodo` | Activities + experiences | ~87 (Yellowstone) | POI table |
| `/visitorcenters` | Visitor centers | ~10–15 | POI table |
| `/campgrounds` | Campgrounds (with campsites, reservation info) | ~10–15 | POI table |
| `/alerts` | Live alerts (closures, advisories) | ~5 | Optional, surface in UI |
| `/amenities` | Reference list of amenity types | 68 (global) | Lookup table |
| `/multimedia/galleries` | Image galleries | ~265 (Yellowstone) | Optional gallery feature |

### Field shapes captured (the key ones)

**`/parks`** — top-level keys observed:
`id` (UUID), `parkCode` (4-letter slug, e.g. `yell`), `fullName`, `name`, `designation`, `description`, `latitude`/`longitude`/`latLong`, `states` (CSV string e.g. `"ID,MT,WY"`), `url`, `directionsInfo`, `directionsUrl`, `weatherInfo`, `activities[]`, `topics[]`, `addresses[]`, `contacts{phoneNumbers[], emailAddresses[]}`, `entranceFees[]`, `entrancePasses[]`, `fees[]`, `operatingHours[]`, `images[]`, `multimedia[]`.

**`/places`** — POI candidate. Keys:
`id`, `title`, `url`, `latitude`/`longitude`, `bodyText` (HTML), `listingDescription`, `images[]`, `tags[]` (strings), `amenities[]` (strings), `quickFacts[]`, `relatedParks[]`, `isOpenToPublic`, `isMapPinHidden`, `isPassportStampLocation`, `audioDescription`.

**`/thingstodo`** — activity-flavored POI. Keys:
`id`, `title`, `url`, `latitude`/`longitude`, `shortDescription`, `longDescription` (HTML), `activities[]`, `topics[]`, `tags[]`, `season[]`, `timeOfDay[]`, `duration`, `durationDescription`, `accessibilityInformation`, `arePetsPermitted`, `doFeesApply`, `feeDescription`, `isReservationRequired`, `relatedParks[]`.

**`/visitorcenters`** — Keys:
`id`, `name`, `parkCode`, `description`, `latitude`/`longitude`, `addresses[]`, `contacts`, `operatingHours[]`, `directionsInfo`, `directionsUrl`, `amenities[]`, `images[]`, `isPassportStampLocation`, `passportStampImages[]`.

**`/campgrounds`** — Keys:
`id`, `name`, `parkCode`, `description`, `latitude`/`longitude`, `addresses[]`, `contacts`, `operatingHours[]`, `campsites{}`, `accessibility{}`, `amenities{}`, `fees[]`, `numberOfSitesReservable`, `numberOfSitesFirstComeFirstServe`, `reservationInfo`, `reservationUrl`, `regulationsOverview`, `regulationsurl`, `weatherOverview`.

**Common gotchas observed in responses:**
- Numbers come back as **strings** (`"44.598..."`, `"35.00"`, `"true"`/`"false"`/`"0"`/`"1"`) — cast on ingest.
- `states` is a CSV string, not an array.
- `bodyText`, `longDescription`, etc. contain **HTML** — store as-is, render safely client-side.
- `latLong` format is inconsistent across endpoints (`"lat:X, long:Y"` vs `"X,Y"` vs `"{lat:X, lng:Y}"`); prefer the discrete `latitude`/`longitude` fields.

## Proposed schema (local tables)

Two layers: **NPS-mirrored tables** (kept fresh by sync jobs) and **user-data tables** (the actual app state).

### Mirror of NPS data

| Table | Purpose |
|---|---|
| `parks` | One row per National Park. Cols: `nps_id` (UUID, unique), `park_code` (unique slug), `name`, `full_name`, `designation`, `description`, `latitude`, `longitude`, `states` (JSON array), `url`, `directions_info`, `directions_url`, `weather_info`, `last_synced_at`. |
| `points_of_interest` | Unified POI table for all four POI-shaped endpoints. Cols: `nps_id` (UUID), `park_id` (FK), `kind` (enum: `place`/`thing_to_do`/`visitor_center`/`campground`), `title`, `description` (sanitized HTML), `latitude`, `longitude`, `url`, `details` (JSON for kind-specific fields like duration, season, campsites, etc.), `is_passport_stamp_location`, `last_synced_at`. Indexes on `(park_id, kind)` and `nps_id`. |
| `images` | Polymorphic. Cols: `imageable_type`, `imageable_id`, `url`, `title`, `alt_text`, `caption`, `credit`, `sort_order`. |
| `addresses` | Polymorphic to park or POI. Cols: `addressable_type/id`, `type` (Physical/Mailing), `line1/2/3`, `city`, `state_code`, `postal_code`, `country_code`. |
| `contacts` | Polymorphic. Cols: `contactable_type/id`, `kind` (phone/email), `value`, `label`. |
| `operating_hours` | Polymorphic. Cols: `subject_type/id`, `name`, `description`, `standard_hours` (JSON), `exceptions` (JSON). |
| `entrance_fees` | `park_id`, `kind` (fee/pass), `title`, `cost`, `description`. |
| `activities` | Reference. `nps_id`, `name`. Many-to-many to `parks` and POIs via pivots. |
| `topics` | Reference. Same shape as activities. |
| `amenities` | Reference. `nps_id`, `name`, `categories` (JSON). Many-to-many to POIs. |
| `tags` | Free-text tag strings from `/places` and `/thingstodo`. Many-to-many to POIs (`taggables` pivot). |
| `alerts` | `park_id`, `nps_id`, `category`, `title`, `description`, `url`, `last_indexed_at`. Optional. |
| `nps_syncs` | Bookkeeping. `entity` (parks/places/etc.), `last_run_at`, `last_success_at`, `last_error`, `record_count`. |

### User-data schema (locked)

Designed in a Q&A session on 2026-06-28. Decisions are reflected below; the underlying rationale per decision is captured in memory (`project_user_data_schema.md`).

| Table | Purpose |
|---|---|
| `users` | Extends Fortify starter kit with `display_name` (string, nullable) and `share_enabled` (bool, default `false`). |
| `visits` | `user_id` FK, `park_id` FK, `started_at`, `ended_at` (nullable → live check-in), `notes` (text, surfaced as **"Journal"** in the UI), timestamps. One row per park visit. |
| `visit_pois` | Per-visit POI checklist. Cols: `visit_id` FK cascade, `point_of_interest_id` FK cascade, `checked_at`, timestamps. Unique on `(visit_id, point_of_interest_id)`. Row exists ⇔ checked; uncheck = delete. |
| `photos` | Polymorphic to visit or visit_poi. Cols: `photoable_type/id`, `disk` (local in dev, s3-compatible in prod), `path`, `original_filename`, `mime`, `size`, `taken_at` (EXIF, nullable), `latitude`/`longitude` (EXIF, nullable), `uploaded_by_user_id` FK, timestamps. |
| `share_tokens` | One per user. Cols: `user_id` FK unique cascade, `token` (random URL-safe, unique), `revoked_at` (nullable), timestamps. One token → one URL → list + map both rendered. |

### Indexes worth calling out
- `parks.park_code` unique
- `points_of_interest.nps_id` indexed; unique on `(nps_id, park_id)` to allow split-park duplicates
- `points_of_interest (park_id, kind)`
- `visits (user_id, started_at)` and `visits (user_id, park_id)`
- `visit_pois (visit_id, point_of_interest_id)` unique
- `share_tokens.user_id` unique, `share_tokens.token` unique

### Sync strategy

- Artisan command `nps:sync {entity?}` — paginated pulls from each endpoint (default limit 50, max 500). Dispatchable as Horizon jobs.
- Per-entity sync runs upserts keyed on `nps_id`; never deletes (NPS occasionally removes records; we tombstone via `archived_at` instead).
- Initial bulk load: ~63 parks, ~3k–30k POIs depending on which endpoints we mirror. Well under daily rate limit.
- Refresh cadence (proposal): parks weekly, POIs monthly, alerts every 15 min.

## Decisions log

| Topic | Decision | Date |
|---|---|---|
| POI scope | All four POI endpoints mirrored into a unified `points_of_interest` table with a `kind` discriminator. | 2026-06-27 |
| Photo storage | Local disk in dev, S3-API-compatible in prod (Laravel Cloud R2). `photos.disk` column captures which. | 2026-06-28 |
| Visit boundary | One visit = one park. | 2026-06-28 |
| Live check-in | `visits.ended_at IS NULL` means live; backdated visits set both timestamps. | 2026-06-28 |
| POI check-off | Row exists ⇔ checked; uncheck = delete. No wishlist. | 2026-06-28 |
| Share URL identity | Token only — no name in URL. | 2026-06-28 |
| Share token scope | One token per user; one URL renders list + map. | 2026-06-28 |
| Visit visibility | Single `users.share_enabled` toggle; no per-visit visibility. | 2026-06-28 |
| Notes scope | Visit-level only, UI label is **"Journal"**. | 2026-06-28 |
| Auth package | **Laravel Fortify** (already installed via the Vue Starter Kit). | 2026-06-28 |
| Passkeys (WebAuthn) | **Keep on** — optional second factor, never required. | 2026-06-28 |
| `display_name` exposure | Surface on the sign-up form AND editable in profile. Not in share URLs. | 2026-06-28 |
| `share_enabled` exposure | Profile page only, default `false`. Not collected at sign-up. | 2026-06-28 |
| Email-code 2FA | Hand-rolled (~50 LOC) as an additional second factor alongside TOTP. | 2026-06-28 |
| Email verification | Required — accounts stay pending until verified. | 2026-06-28 |
| Sanctum | Yes — for the mobile API tokens. | 2026-06-28 |
| Honeypot | `spatie/laravel-honeypot` on the registration form. | 2026-06-28 |
| Email-code 2FA mechanism | Built as an **alternate way to pass the existing Fortify two-factor challenge** (offered to anyone who reaches `/two-factor-challenge`), not a standalone login trigger. Code is a 6-digit, cache-stored hash with a 10-min TTL. | 2026-06-28 |
| Honeypot wiring | Added `ProtectAgainstSpam` to `config/fortify.php` `middleware` (no-op unless honeypot fields are present, so only the register form is gated). | 2026-06-28 |
| Sanctum scope (so far) | Foundation only — `HasApiTokens` + `personal_access_tokens` table installed. Token-issuing API endpoints belong to the mobile-API slice (not built yet). | 2026-06-28 |
| Mobile token API | **Built 2026-06-30.** `routes/api.php` wired in `bootstrap/app.php`; `sanctum` guard added to `config/auth.php`. Endpoints: `login`, `two-factor-challenge`, `two-factor-challenge/email-code`, `logout`, `user`. Login mirrors Fortify — returns `{two_factor, challenge_token}` (cache-backed, 10-min TTL, replaces session `login.id`) instead of a token when TOTP is enabled. | 2026-06-30 |
| API 2FA scope | **Full parity** — the challenge endpoint accepts a TOTP code, the hand-rolled email code, or a recovery code (consumed on use). TOTP verification is wrapped so a malformed secret can't crash the request. | 2026-06-30 |
| API token lifetime | **Never expire** (Sanctum default; no `config/sanctum.php` expiration). The app holds a token until logout/revoke. | 2026-06-30 |
| API resource wrapping | `JsonResource::withoutWrapping()` — the mobile API returns bare JSON objects (no top-level `data` key), consistent for nested and top-level resources. | 2026-06-30 |
| API rate limits | `api-auth` (10/min by IP) on the unauthenticated auth endpoints; `api` (60/min by user/IP) on authenticated endpoints. Defined in `AppServiceProvider`. | 2026-06-30 |
| Map provider | **Leaflet + OpenStreetMap** (free, no API key/token, dependency-light). Revisit Mapbox only for vector tiles / richer styling. | 2026-06-29 |
| Brand color | Centralized as `brand-{300,400,700,800}` tokens in `resources/css/app.css` (repoint to rebrand); UI uses `brand-*`, never a raw palette. Originally aliased to emerald; **repointed to the passport pine green `#2f7d46` on 2026-07-04**. | 2026-06-29 |
| Design system | **Passport theme (2026-07-04):** paper & pine palette (light) + "passport night" (dark), green primary/ring, region-colored charts. Type roles: **Fraunces** serif display (h1/h2), Instrument Sans body, monospace for data/tabular figures. Tokens in `resources/css/app.css`; Fraunces self-hosted via the Vite/Bunny font pipeline. | 2026-07-04 |
| Site name | **NationalParks.me** (`APP_NAME` → titles, mail from-name, brand wordmarks). Descriptive "National Parks" copy (parks list heading, visit tallies) refers to the parks themselves and is left as-is. | 2026-07-04 |
| Closure indicators | **Built 2026-06-30** as closures-only: a red "Closure" chip on cards (list + shared page) and a red ring on map pins, driven by a `Park::scopeWithClosureStatus()` subquery surfaced through `SummarizePark` as a `closed` flag. | 2026-06-29 |
| Stamps — UI naming | Called **"Stamps"** everywhere (UI + code + tables), leaning into the NPS Passport program. | 2026-07-04 |
| Stamps — collections | One collection **per state/territory that has a national park** (fully cover the state), plus milestones and the 8 NPS Passport regions. Membership derived from `parks.states`; not hand-listed. | 2026-07-04 |
| Stamps — regions | Use the **Passport to Your National Parks** 9-region scheme (8 usable; National Capital has no parks), each in its official color — not the administrative 7/12-region schemes. | 2026-07-04 |
| Stamps — earning | Sticky (never revoked); progress computed live; awarded on check-in + a `stamps:evaluate` backfill. Versioning via `members_changed_at` → "vintage" year labels. | 2026-07-04 |
| Frontend tests | **Vitest + Vue Test Utils** (unit, in pre-push + CI) and **Playwright E2E** (one journey, own CI workflow). Both were named in the stack but not configured until now. | 2026-07-04 |

**Auth slice status:** the auth rows above were implemented 2026-06-28. Broader status: see **Implementation status** near the top of Functionality. Rationale archives in memory: `project_auth_stack_decisions.md`, `project_user_data_schema.md`.

## Open questions

1. ~~**Map provider:** Mapbox vs. Leaflet + OpenStreetMap.~~ **Resolved 2026-06-29 → Leaflet + OpenStreetMap** (free, no API key/token, dependency-light). Revisit Mapbox only if we want vector tiles / richer styling later.
