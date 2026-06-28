---
paths:
  - app/Exceptions/**/*.php
  - bootstrap/app.php
---

# Error Handling Rules

## Exception Reporting

Co-locate reporting logic on the exception class:

```php
class PaymentFailedException extends Exception
{
    public function report(): void
    {
        Log::channel('payments')->error($this->getMessage());
    }

    public function render(Request $request): Response
    {
        return response()->json(['error' => 'Payment failed'], 422);
    }
}
```

## Suppress Reporting with `ShouldntReport`

```php
class ValidationException extends Exception implements ShouldntReport
{
    // Will not be sent to error tracking
}
```

## Throttle High-Volume Exceptions

```php
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->throttle(fn (Throwable $e) => match (true) {
        $e instanceof ExternalApiException => Limit::perMinute(5),
        default => Limit::none(),
    });
})
```

## Structured Context

Add context to exceptions for better debugging:

```php
class OrderProcessingException extends Exception
{
    public function context(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
        ];
    }
}
```

## Prevent Duplicate Reports

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReportDuplicates();
})
```
