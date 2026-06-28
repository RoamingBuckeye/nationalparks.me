<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;
use Stringable;

final readonly class ParkCode implements Stringable
{
    public const string PATTERN = '/^[a-z]{4}$/';

    public function __construct(public string $value)
    {
        if (preg_match(self::PATTERN, $value) !== 1) {
            throw new InvalidArgumentException("Invalid NPS park code: '{$value}' (expected 4 lowercase letters)");
        }
    }

    public static function tryFrom(string $value): ?self
    {
        return preg_match(self::PATTERN, $value) === 1
            ? new self($value)
            : null;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
