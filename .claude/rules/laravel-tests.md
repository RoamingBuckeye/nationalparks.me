---
paths: tests/**/*.php
---

# Test Rules

## Pest Syntax

Use `it()` with `expect()`:

```php
it('creates user via API', function () {
    $response = $this->postJson('/api/users', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'name', 'email']);
});
```

## Fakes Over Mocks

```php
it('sends welcome email', function () {
    Mail::fake();

    app(UserService::class)->createUser($userData);

    Mail::assertSent(WelcomeEmail::class);
});
```

## Factories with States

Use factory states rather than manually setting attributes:

```php
$user = User::factory()->inactive()->create();
```

## Use `LazilyRefreshDatabase`

Prefer over `RefreshDatabase` — only runs migrations if the database is actually used:

```php
uses(LazilyRefreshDatabase::class);
```

## `assertModelExists`

```php
// ✅
$this->assertModelExists($user);

// ❌
$this->assertDatabaseHas('users', ['id' => $user->id]);
```

## `Exceptions::fake()`

```php
Exceptions::fake();

// Trigger code that throws...

Exceptions::assertReported(PaymentFailedException::class);
```

## `recycle()` for Shared Relations

Share a single related model across multiple factories:

```php
$user = User::factory()->create();
$posts = Post::factory(3)->recycle($user)->create();
```
