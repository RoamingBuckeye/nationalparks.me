---
paths: app/Policies/**/*.php
---

# Policy Rules

Policy methods receive the authenticated user and the model instance:

```php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
// Usage: $this->authorize('update', $post);
```
