---
paths: database/migrations/**/*.php
---

# Migration Rules

## Only Define `up()` — No `down()`

Rollbacks in production are risky. To undo a change, create a new migration.

```php
// ✅
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->timestamps();
    });
}

// ❌ Don't define down()
```

## No Foreign Key Constraints

Use plain columns with indexes instead of database-level FK constraints:

```php
// ✅
$table->unsignedBigInteger('user_id');
$table->index('user_id');

// ❌
$table->foreignId('user_id')->constrained();
```

## No Database Enums

Use string columns — define the allowed values in a PHP enum and cast in the model:

```php
// ✅
$table->string('status');

// ❌
$table->enum('status', ['pending', 'approved']);
```

## Add Indexes on Queried Columns

```php
$table->index(['user_id', 'published_at']);
```
