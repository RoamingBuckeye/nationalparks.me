---
paths: **/*.php
---

# HTTP Client Rules

## Always Set Timeouts

Every request must have explicit `timeout` and `connectTimeout`:

```php
Http::timeout(30)
    ->connectTimeout(5)
    ->get('https://api.example.com/data');
```

## Retry with Backoff

```php
Http::retry(3, 100, throw: false)
    ->get('https://api.example.com/data');
```

## Concurrent Requests

Use `Http::pool()` for independent requests:

```php
$responses = Http::pool(fn (Pool $pool) => [
    $pool->get('https://api.example.com/users'),
    $pool->get('https://api.example.com/posts'),
]);
```

## Check Response Status

Always check the response or use `throw()`:

```php
$response = Http::get('https://api.example.com/data');
$response->throw();

// Or check explicitly
if ($response->successful()) {
    // ...
}
```

## Testing

Use `Http::fake()` with `preventStrayRequests()`:

```php
Http::fake([
    'api.example.com/*' => Http::response(['data' => 'value']),
]);
Http::preventStrayRequests();
```
