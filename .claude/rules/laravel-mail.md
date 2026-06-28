---
paths: app/Mail/**/*.php
---

# Mail Rules

## Always Queue Mailables

Implement `ShouldQueue` directly on the Mailable:

```php
class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable;
}
```

## Use `afterCommit()` in Transactions

Prevent sending if the transaction rolls back:

```php
Mail::to($user)->send(
    (new OrderConfirmation($order))->afterCommit()
);
```

## Test with `assertQueued`

For queued mailables, assert against the queue not the mailer:

```php
// ✅
Mail::assertQueued(WelcomeEmail::class);

// ❌ Won't work for queued mailables
Mail::assertSent(WelcomeEmail::class);
```

## Markdown Mailables for Transactional Emails

Use Markdown mailables for consistent styling:

```php
public function content(): Content
{
    return new Content(
        markdown: 'emails.orders.shipped',
    );
}
```
