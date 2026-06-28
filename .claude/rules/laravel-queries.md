---
paths: **/*.php
---

# Advanced Query Rules

## `addSelect()` Subqueries

Fetch a single value from a has-many relationship without eager loading:

```php
User::addSelect([
    'last_login_at' => Login::select('created_at')
        ->whereColumn('user_id', 'users.id')
        ->latest()
        ->limit(1),
])->get();
```

## `whereIn` + Subquery Over `whereHas`

More performant for large datasets:

```php
// ✅
Post::whereIn('user_id', User::select('id')->where('active', true))->get();

// ❌ Generates correlated subquery
Post::whereHas('user', fn ($q) => $q->where('active', true))->get();
```

## Conditional Aggregates

```php
Order::selectRaw("
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
")->first();
```

## Compound Indexes

Match index column order to your `orderBy`:

```php
// Migration
$table->index(['user_id', 'created_at']);

// Query — columns match index order
Post::where('user_id', $id)->orderBy('created_at', 'desc')->get();
```
