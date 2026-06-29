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
| Tailwind CSS | 4 | via `@tailwindcss/vite` |
| TypeScript | strict | starter kit defaults |
| axios | latest | re-added; Inertia 3 dropped it but NativePHP needs it |
| Testing | Vitest + Vue Test Utils | (frontend); PHPUnit (backend) |

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
- NPS Data API key (functional dependency, not stack) — Bill to locate existing key
- Apple Developer Program enrollment — required when we're ready to test on a real iPhone

## Functionality

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

**Status (2026-06-29):** the web core visit loop is built — park list (`/parks`, visited state + search/state/visited filters), park detail (`/parks/{park}`, POI kind counts + your visits + check-in / log-past-visit), and the visit page (`/visits/{visit}`, editable dates + Journal, live/end toggle, paginated POI checklist with per-visit check-off). Dashboard shows real stats. Photo upload is **not yet built** (next slice).

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

### Sharing

- A user can generate a **public share link** for their list and/or map
- The shared page is read-only and doesn't reveal account/profile data beyond a display name
- The link is revocable; the user can rotate/disable it from their settings

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

**Auth slice status:** all rows above are implemented as of 2026-06-28; full suite green (134 Pest tests). Rationale archive: memory `project_auth_stack_decisions.md`.

## Open questions

1. **Map provider:** Mapbox (paid, free tier ~50k loads/mo, great default styles) vs. Leaflet + OpenStreetMap (free, lower polish). Likely answered when we start the map UI.
