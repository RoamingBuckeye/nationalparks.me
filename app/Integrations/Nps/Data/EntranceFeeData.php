<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

use App\Integrations\Nps\Enums\FeeKind;

final readonly class EntranceFeeData
{
    public function __construct(
        public FeeKind $kind,
        public string $title,
        public float $cost,
        public ?string $description,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row, FeeKind $kind): self
    {
        return new self(
            kind: $kind,
            title: (string) ($row['title'] ?? ''),
            cost: is_numeric($row['cost'] ?? null) ? (float) $row['cost'] : 0.0,
            description: self::nullableString($row['description'] ?? null),
        );
    }

    /**
     * @param  list<array<string, mixed>>|null  $rows
     * @return list<self>
     */
    public static function listFrom(?array $rows, FeeKind $kind): array
    {
        if ($rows === null) {
            return [];
        }

        return array_map(
            fn (array $row): self => self::fromArray($row, $kind),
            $rows,
        );
    }

    protected static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);

        return $str === '' ? null : $str;
    }
}
