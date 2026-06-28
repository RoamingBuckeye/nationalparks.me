<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

final readonly class AmenityData
{
    /** @param list<string> $categories */
    public function __construct(
        public string $npsId,
        public string $name,
        public array $categories,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            npsId: (string) ($row['id'] ?? ''),
            name: (string) ($row['name'] ?? ''),
            categories: self::stringList($row['categories'] ?? null),
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

    /** @return list<string> */
    protected static function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $entry) {
            $str = trim((string) $entry);
            if ($str !== '') {
                $out[] = $str;
            }
        }

        return $out;
    }
}
