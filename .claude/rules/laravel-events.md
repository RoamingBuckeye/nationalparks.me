---
paths:
  - app/Events/**/*.php
  - app/Listeners/**/*.php
  - app/Notifications/**/*.php
---

# Event & Notification Rules

## Event Discovery

Let Laravel auto-discover events and listeners — no manual registration needed.

Cache events in production:

```bash
php artisan event:cache
```

## Dispatch After Commit

When dispatching events inside a transaction, use `ShouldDispatchAfterCommit`:

```php
class OrderPlaced implements ShouldDispatchAfterCommit
{
    // ...
}
```

## Always Queue Notifications

```php
class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;
}
```

## Notifications in Transactions

Use `afterCommit()` to prevent sending if the transaction rolls back:

```php
$user->notify(
    (new InvoicePaid($invoice))->afterCommit()
);
```

## On-Demand Notifications

For non-user recipients:

```php
Notification::route('mail', 'support@example.com')
    ->notify(new SystemAlert($message));
```
