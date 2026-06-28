---
paths: app/Http/Resources/**/*.php
---

# API Resource Rules

Define `toArray()` and use `whenLoaded()` for relationships:

```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}
```

HTTP status codes:

| Code | Use |
|------|-----|
| 200 | Success |
| 201 | Created |
| 204 | Deleted (no content) |
| 404 | Not found |
| 422 | Validation error |
