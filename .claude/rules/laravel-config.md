---
paths: config/**/*.php
---

# Config File Rules

`env()` is allowed here — this is the only place it should appear.

## Comma-Separated ENV Lists

Map to arrays in config, never parse in application code:

```php
// config/app.php
'allowed_origins' => env('ALLOWED_ORIGINS', '*') === '*'
    ? ['*']
    : array_filter(array_map('trim', explode(',', env('ALLOWED_ORIGINS', '')))),
```

## No Closures in Config Values

Config must be serializable for `php artisan config:cache`. Closures will break caching.

## Environment Checks

Use `App::environment()` instead of comparing config values:

```php
// ✅
if (App::environment('production')) { ... }

// ❌
if (config('app.env') === 'production') { ... }
```
