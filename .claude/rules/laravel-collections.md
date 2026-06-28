---
paths: **/*.php
---

# Collection Rules

## Higher-Order Messages

For simple operations, use higher-order messages:

```php
// ✅
$users->each->activate();
$names = $users->map->name;

// ❌
$users->each(fn ($user) => $user->activate());
```

## `cursor()` vs `lazy()`

Use `cursor()` for memory-efficient read-only iteration (single query, one model at a time):

```php
User::where('active', true)->cursor()->each(fn ($user) => ...);
```

Use `lazy()` when you need eager-loaded relationships (queries in chunks):

```php
User::with('posts')->lazy()->each(fn ($user) => ...);
```

## `lazyById()` When Updating

Prevents skipping rows when modifying records during iteration:

```php
User::lazyById(200)->each(function ($user) {
    $user->update(['verified' => true]);
});
```

## `toQuery()` for Bulk Operations

Convert a collection back to a query for efficient bulk updates:

```php
$inactiveUsers = User::where('last_login', '<', now()->subYear())->get();
$inactiveUsers->toQuery()->update(['status' => 'inactive']);
```
