# nationalparks.me

Track your visits to all 63 U.S. National Parks — check in live or backdate past trips, tick off the points of interest you experienced, keep a journal, attach photos, and share a read-only list and map with friends.

A single Laravel codebase serves two targets from the same source: a **web app** and **native iOS/Android apps** packaged via [NativePHP for Mobile](https://nativephp.com). Routes, controllers, models, Inertia pages, and Vue components are shared; only the runtime concerns that genuinely diverge (database, storage, auth transport, background jobs) switch via config.

> [!NOTE]
> This project is under active development. `PLAN.md` is the canonical reference for the stack, functionality, and schema design — start there for the full picture.

## Overview

| Layer | Technology |
|---|---|
| Framework | Laravel 13, PHP 8.5 |
| Frontend | Vue 3 (Composition API, TypeScript strict) + Inertia.js 3 |
| Styling | Tailwind CSS 4 |
| Build | Vite + `laravel-vite-plugin`, Wayfinder typed routes |
| Auth | Laravel Fortify (email/password, TOTP, email-code 2FA, passkeys) + Sanctum for mobile tokens |
| Queue | Laravel Horizon (Redis) — web only |
| Mobile | NativePHP for Mobile 3 (`me.nationalparks.app`) |
| Data source | [NPS Data API](https://www.nps.gov/subjects/developer/api-documentation.htm) |

### How the dual target works

| Concern | Web | Mobile |
|---|---|---|
| Where PHP runs | Server container (dev) → cloud (prod) | On-device, bundled runtime |
| Database | MySQL | Local SQLite, syncs to web API |
| File storage | Server disk / S3-compatible | Device sandbox |
| Auth | Sessions + cookies | Sanctum tokens against the web API |
| Background jobs | Horizon (Redis) | Deferred to web |
| Network | Always assumed | Offline-first reads, sync when online |

## Functionality

- **Accounts** — email/password sign-up with an optional display name. Email verification is required; accounts stay pending until verified. Bot deterrence via Spatie Honeypot on the registration form.
- **Multi-factor auth (optional, per user)** — TOTP via an authenticator app, email-code as an alternate second factor, and passkeys (WebAuthn). None are ever required.
- **Log a visit** — check in live (now) or log a backdated past visit. One visit is one park, with dates, a free-text journal, and photos.
- **Check off points of interest** — each park carries a curated list of POIs (places, things to do, visitor centers, campgrounds) mirrored from the NPS API. Per visit, tick off what you experienced; attach photos to the visit or to individual POIs.
- **Visualize progress** — a list view of all 63 parks with visited/unvisited state, visit counts, and last-visited dates; a U.S. map with parks pinned and color-coded by status. Filter and sort by state, visited status, or last-visit date.
- **Share** — generate a public, read-only share link rendering your list and map. It exposes nothing beyond a display name, and is revocable/rotatable from settings.

### NPS data sync

Park and POI data is mirrored from the NPS Data API into local tables and kept fresh by an Artisan command:

```bash
# Sync all entities, or a single one (parks, places, thingstodo, visitorcenters, campgrounds, alerts)
php artisan nps:sync
php artisan nps:sync parks
```

Syncs upsert on the NPS UUID and never hard-delete. Proposed refresh cadence: parks weekly, POIs monthly, alerts every 15 minutes.

## Local setup

### Prerequisites

- PHP 8.5, Composer 2.9+
- Node 22+ and npm
- Docker (for [Laravel Sail](https://laravel.com/docs/sail), which provides MySQL, Redis, and Mailpit)
- An [NPS Data API key](https://www.nps.gov/subjects/developer/get-started.htm) (free; required for data sync)

### Steps

1. **Install dependencies and bootstrap the environment.** The `setup` script installs Composer and npm packages, copies `.env`, generates an app key, runs migrations, and builds assets:

   ```bash
   composer setup
   ```

   To do it by hand instead:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   npm install
   ```

2. **Configure `.env`.** The default `.env.example` uses SQLite, which works out of the box for a quick start. For the full Sail stack (MySQL + Redis + Mailpit), set `DB_CONNECTION=mysql` and the matching `DB_*`, `REDIS_*`, and `MAIL_*` values that `compose.yaml` expects. Add your NPS key:

   ```dotenv
   NPS_API_KEY=your-key-here
   ```

3. **Run migrations** (skip if `composer setup` already did):

   ```bash
   php artisan migrate
   ```

4. **Start the dev environment.** This runs the PHP server, queue worker, log tailer, and Vite together:

   ```bash
   composer dev
   ```

   Or with the full Docker stack:

   ```bash
   ./vendor/bin/sail up -d
   ./vendor/bin/sail npm run dev
   ```

   The app is at `http://localhost`, Vite HMR on `5173`, and Mailpit's inbox UI at `http://localhost:8025`.

5. **Seed park data** from the NPS API once the app is running:

   ```bash
   php artisan nps:sync
   ```

### Install the git hooks (recommended)

```bash
composer hooks:install
```

### Mobile builds

The iOS and Android shells are generated by NativePHP and live in `nativephp/` (gitignored). Drive them with the `native` wrapper, which proxies to the `native:*` Artisan commands:

```bash
./native install   # generate the native projects
./native run        # build and launch on a simulator/device
```

> [!IMPORTANT]
> iOS builds need Xcode + CocoaPods (`brew install cocoapods`); Android builds need Android Studio with its bundled JDK/Gradle. Store distribution requires an Apple Developer Program membership and a Google Play Console account.

## Quality checks

```bash
composer lint        # Pint (auto-fix)
composer lint:check  # Pint (dry run)
composer types:check # PHPStan / Larastan
composer test        # config clear + lint + types + Pest
composer ci:check    # the full CI gate (JS lint/format/types + PHP test)

npm run lint         # ESLint (auto-fix)
npm run types:check  # vue-tsc
```

## Deployment

The web app targets **[Laravel Cloud](https://cloud.laravel.com)**, with R2 (S3-API-compatible) for photo storage in production.

1. **Provision** an environment with a MySQL database, a Redis cache/queue, and an R2 bucket. Point `FILESYSTEM_DISK` at the S3-compatible disk and fill the `AWS_*` credentials for R2.
2. **Set production env vars** — `APP_ENV=production`, `APP_DEBUG=false`, a generated `APP_KEY`, real `DB_*` / `REDIS_*` / `MAIL_*` values, and `NPS_API_KEY`.
3. **Build and migrate** on deploy:

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan config:cache
   php artisan event:cache
   npm ci && npm run build
   php artisan migrate --force
   ```

4. **Run Horizon** as a long-lived process for queues, and schedule the NPS sync via the Laravel scheduler (`php artisan schedule:run` every minute, or Cloud's scheduler).
5. **Seed park data** on first deploy with `php artisan nps:sync`.

> [!TIP]
> If you're deploying through the Laravel Cloud CLI, the `/deploying-laravel-cloud` skill walks through environments, databases, caches, domains, and background processes.

## Project layout

```
app/
  Actions/        Single-responsibility invokable classes
  Console/        Artisan commands (NpsSyncCommand)
  Domain/         Domain models and value objects
  Http/           Controllers, requests, resources
  Integrations/   NPS API client
  Models/         Eloquent models
database/migrations/  Schema (NPS mirror + user-data tables)
resources/        Vue/Inertia pages and components
routes/           web.php, settings.php, console.php
nativephp/        Generated iOS/Android shells (gitignored)
PLAN.md           Canonical stack, functionality, and schema reference
```

Coding conventions are enforced by the rules in `.claude/rules/` (imported via `CLAUDE.md`) and by Pint, PHPStan, and ESLint.
