---
paths: **/*.php
---

# Caching Rules

## Use `Cache::remember()`

Never manually check-then-store:

```php
// ✅
$users = Cache::remember('active-users', 3600, fn () => User::active()->get());

// ❌
$users = Cache::get('active-users');
if ($users === null) {
    $users = User::active()->get();
    Cache::put('active-users', $users, 3600);
}
```

## Stale-While-Revalidate

Use `Cache::flexible()` to serve stale data while refreshing in the background:

```php
$users = Cache::flexible('users', [300, 600], fn () => User::all());
```

## Per-Request Memoization

Use `once()` to avoid redundant work within a single request:

```php
public function settings(): Settings
{
    return once(fn () => Settings::load());
}
```

## Cache Tags for Group Invalidation

```php
Cache::tags(['posts', 'users'])->put('key', $value, 3600);
Cache::tags(['posts'])->flush();
```

## Atomic Conditional Writes

Use `Cache::add()` to write only if the key doesn't exist:

```php
Cache::add('processing-order-' . $order->id, true, 300);
```
