---
paths: app/Models/**/*.php
---

# Eloquent Model Rules

## Mass Assignment

Use `$guarded` not `$fillable`:

```php
// ✅
protected $guarded = [];

// ❌
protected $fillable = ['name', 'email'];
```

## Relationships

```php
public function posts(): HasMany
{
    return $this->hasMany(Post::class);
}

public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class)
        ->withTimestamps()
        ->withPivot('assigned_at');
}
```

## Query Scopes

```php
public function scopePublished(Builder $query): void
{
    $query->where('published_at', '<=', now());
}
// Usage: Post::published()->get()
```

## Prevent N+1

```php
// ✅
$users = User::with(['posts', 'profile'])->get();

// ❌
foreach (User::all() as $user) {
    echo $user->profile->bio; // query per user
}
```

## Enums

Never use database enum columns. Use string column + PHP enum + cast:

```php
// migration
$table->string('status');

// enum
enum OrderStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
}

// model cast
protected function casts(): array
{
    return ['status' => OrderStatus::class];
}
```

## `whereBelongsTo`

```php
// ✅
Post::whereBelongsTo($user)->get();

// ❌
Post::where('user_id', $user->id)->get();
```

## Date Casting

Cast date columns with Carbon:

```php
protected function casts(): array
{
    return [
        'published_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];
}
```
