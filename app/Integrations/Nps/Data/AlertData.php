<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

use App\Integrations\Nps\Enums\AlertCategory;
use Carbon\CarbonImmutable;

final readonly class AlertData
{
    public function __construct(
        public string $npsId,
        public ?string $parkCode,
        public ?AlertCategory $category,
        public string $title,
        public ?string $description,
        public ?string $url,
        public ?CarbonImmutable $lastIndexedAt,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            npsId: (string) ($row['id'] ?? ''),
            parkCode: self::nullableString($row['parkCode'] ?? null),
            category: AlertCategory::tryFromLabel(self::nullableString($row['category'] ?? null)),
            title: (string) ($row['title'] ?? ''),
            description: self::nullableString($row['description'] ?? null),
            url: self::nullableString($row['url'] ?? null),
            lastIndexedAt: self::nullableDate($row['lastIndexedDate'] ?? null),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<self>
     */
    public static function listFrom(array $rows): array
    {
        return array_values(array_map(
            fn (array $row): self => self::fromArray($row),
            $rows,
        ));
    }

    protected static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);

        return $str === '' ? null : $str;
    }

    protected static function nullableDate(mixed $value): ?CarbonImmutable
    {
        $str = self::nullableString($value);
        if ($str === null) {
            return null;
        }

        try {
            return CarbonImmutable::parse($str);
        } catch (\Throwable) {
            return null;
        }
    }
}
