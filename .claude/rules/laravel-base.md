---
paths: **/*.php
---

# Laravel Base Rules

## Dependency Injection

Always use constructor injection:

```php
public function __construct(
    protected readonly UserService $userService,
) {}
```

Bind in service providers:

```php
$this->app->singleton(UserService::class);
$this->app->bind(PaymentGatewayInterface::class, StripePaymentGateway::class);
```

## Environment Variables

Only call `env()` inside config files. Use `config()` everywhere else:

```php
// ✅ config/services.php
'stripe_key' => env('STRIPE_KEY'),

// ✅ application code
$key = config('services.stripe_key');

// ❌ never in application code
$key = env('STRIPE_KEY'); // Returns null after config:cache!
```

## Action Pattern

Single-responsibility classes in `app/Actions/` using `__invoke()`. Reach for an action when logic is needed in multiple places (controllers, commands, jobs) or when a controller method would otherwise grow complex.

```php
// Inject via DI:
public function store(CreatePostRequest $request, PublishPost $publishPost): PostResource
{
    $post = Post::create($request->validated());
    $publishPost($post);

    return new PostResource($post);
}

// Or resolve manually:
resolve(PublishPost::class)($post);
```

Name as `{Verb}{Resource}` (`PublishPost`, `CreateUser`) or just `{Verb}` when context is clear.

## Pipeline Pattern

Process data through a series of steps:

```php
Pipeline::send($request)
    ->through([ValidateInput::class, CheckPermissions::class])
    ->then(fn ($request) => $this->process($request));
```

## Gates

For non-model authorization, define gates in a service provider:

```php
Gate::define('manage-settings', fn (User $user) => $user->isAdmin());
// Usage: $this->authorize('manage-settings');
```

## Atomic Locks

Prevent race conditions in concurrent operations:

```php
Cache::lock('processing-order-' . $order->id, 10)->block(5, function () use ($order) {
    // Only one process can execute this at a time
});
```

## Post-Response Processing

Use `defer()` for work that doesn't affect the response:

```php
defer(fn () => Analytics::track('page.viewed', $data));
```

## Request-Scoped Data

Use the `Context` facade instead of singletons or request attributes:

```php
Context::add('request_id', Str::uuid());
$requestId = Context::get('request_id');
```

## Parallel Execution

```php
[$users, $posts] = Concurrency::run([
    fn () => User::all(),
    fn () => Post::published()->get(),
]);
```
