<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

final readonly class ImageData
{
    public function __construct(
        public string $url,
        public ?string $title,
        public ?string $altText,
        public ?string $caption,
        public ?string $credit,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            url: (string) ($row['url'] ?? ''),
            title: self::nullableString($row['title'] ?? null),
            altText: self::nullableString($row['altText'] ?? null),
            caption: self::nullableString($row['caption'] ?? null),
            credit: self::nullableString($row['credit'] ?? null),
        );
    }

    /**
     * @param  list<array<string, mixed>>|null  $rows
     * @return list<self>
     */
    public static function listFrom(?array $rows): array
    {
        if ($rows === null) {
            return [];
        }

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
}
