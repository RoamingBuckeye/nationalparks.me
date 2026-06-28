---
paths: app/Http/Controllers/**/*.php
---

# Controller Rules

Controllers must be either standard REST or single-action invokable — never both.

## REST Controller

Only the 7 standard methods: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.

```php
class PostController
{
    public function index() {}
    public function store() {}
    public function show(Post $post) {}
    public function update(Post $post) {}
    public function destroy(Post $post) {}
}
```

## Single-Action Invokable

For any action beyond REST methods, create a dedicated invokable controller:

```php
// ✅
class PublishPostController
{
    public function __invoke(Post $post): void {}
}

// ❌ Don't add custom methods to REST controllers
class PostController
{
    public function publish() {} // should be PublishPostController
}
```
