---
paths: app/Actions/**/*.php
---

# Action Rules

Actions are single-responsibility classes in `app/Actions/` that use `__invoke()`.

- Name as `{Verb}{Resource}` (e.g. `PublishPost`, `CreateUser`) or just `{Verb}` when context is clear
- Inject collaborators via constructor
- Wrap multi-step logic in `DB::transaction()` when needed
- May return a value or void

```php
class PublishPost
{
    public function __construct(
        protected Twitter $twitter,
    ) {}

    public function __invoke(Post $post): void
    {
        // publish logic
    }
}

// Resolve and call:
resolve(PublishPost::class)($post);

// Or via DI:
public function store(CreatePostRequest $request, PublishPost $publishPost): PostResource
{
    $post = Post::create($request->validated());
    $publishPost($post);

    return new PostResource($post);
}
```
