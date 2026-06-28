---
paths: **/*.php
---

# PHP Base Rules

## File Structure

```php
<?php

declare(strict_types=1);

namespace Your\Namespace\Here;

use App\Models\User;
```

- Always `<?php` then `declare(strict_types=1);`
- Never include closing `?>` tag
- Imports in alphabetical order

## Naming

| Element | Format |
|---------|--------|
| Classes/Interfaces/Traits/Enums | PascalCase |
| Methods | camelCase verb phrases |
| Properties | camelCase |
| Constants | UPPER_SNAKE_CASE |

Never: single-letter vars (except `$i/$j/$k`), abbreviations (`$usr`, `$cfg`), generic names (`Manager`, `Helper`).

## Type Safety

All parameters and return types must be declared:

```php
public function processPayment(int $userId, float $amount): bool
```

## PHPDoc — Types Only

Only use PHPDoc for types PHP syntax can't express:

```php
/** @param array<string, mixed> $data */
/** @return Collection<int, User> */
/** @param array{name: string, age: int} $userData */
```

Never include descriptions or `@param` annotations that duplicate the type hint.

## Visibility

- Default to `protected` — enables extension and testing
- Use `private` only when truly internal
- Always explicit — never rely on default visibility

## Methods

- Max 30 lines per method
- Use early returns and guard clauses over nested conditions

```php
// ✅
if ($order->isEmpty()) {
    return false;
}

// ❌ nested
if (! $order->isEmpty()) {
    if ($this->validateInventory($order)) { ... }
}
```

## Error Handling

Use specific exception types:

```php
throw new InvalidArgumentException('Amount must be positive');
throw new UnauthorizedException('User lacks required permissions');
// ❌ throw new Exception('Something went wrong');
```
