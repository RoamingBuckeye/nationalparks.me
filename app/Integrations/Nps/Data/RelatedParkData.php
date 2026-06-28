<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

final readonly class RelatedParkData
{
    /** @param list<string> $states */
    public function __construct(
        public string $parkCode,
        public string $fullName,
        public string $name,
        public string $designation,
        public string $url,
        public array $states,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            parkCode: (string) ($row['parkCode'] ?? ''),
            fullName: (string) ($row['fullName'] ?? ''),
            name: (string) ($row['name'] ?? ''),
            designation: (string) ($row['designation'] ?? ''),
            url: (string) ($row['url'] ?? ''),
            states: self::statesFromCsv((string) ($row['states'] ?? '')),
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

    /** @return list<string> */
    protected static function statesFromCsv(string $csv): array
    {
        if ($csv === '') {
            return [];
        }

        $parts = array_filter(array_map('trim', explode(',', $csv)), static fn (string $s): bool => $s !== '');

        return array_values($parts);
    }
}
