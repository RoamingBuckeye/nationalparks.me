---
paths: routes/**/*.php
---

# Routing Rules

## Implicit Route Model Binding

Let Laravel resolve models automatically:

```php
// Route
Route::get('/posts/{post}', [PostController::class, 'show']);

// Controller — $post is resolved automatically
public function show(Post $post): PostResource
{
    return new PostResource($post);
}
```

## Scoped Bindings for Nested Resources

```php
Route::get('/users/{user}/posts/{post}', [UserPostController::class, 'show'])
    ->scopeBindings();
```

## Use Resource Routes

```php
Route::resource('posts', PostController::class);
Route::apiResource('posts', PostController::class);
```

## Type-Hint Form Requests

Validation runs automatically when the request is type-hinted:

```php
public function store(CreatePostRequest $request): PostResource
{
    // Validation already passed — $request->validated() is safe
}
```
