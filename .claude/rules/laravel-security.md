---
paths: **/*.php
---

# Security Rules

## Authorize Every Action

Use policies or gates for every state-changing operation:

```php
$this->authorize('update', $post);
```

## Prevent SQL Injection

Never interpolate user input into raw queries:

```php
// ✅
User::whereRaw('LOWER(name) = ?', [strtolower($input)]);

// ❌
User::whereRaw("LOWER(name) = '$input'");
```

## XSS Prevention

Always use `{{ }}` in Blade — only use `{!! !!}` when explicitly rendering trusted HTML:

```php
// ✅ Escaped
{{ $user->name }}

// ⚠️ Only for trusted, sanitized HTML
{!! $post->rendered_html !!}
```

## Rate Limit Auth & API Routes

```php
RateLimiter::for('login', fn (Request $request) =>
    Limit::perMinute(5)->by($request->ip())
);
```

## File Upload Validation

Always validate MIME type, extension, and size:

```php
'avatar' => ['required', 'file', 'mimes:jpg,png', 'max:2048'],
```

## Encrypt Sensitive Fields

```php
protected function casts(): array
{
    return [
        'ssn' => 'encrypted',
        'api_token' => 'encrypted',
    ];
}
```

## Dependency Auditing

Run `composer audit` regularly to check for known vulnerabilities.
