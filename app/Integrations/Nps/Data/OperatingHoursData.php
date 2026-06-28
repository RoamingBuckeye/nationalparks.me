<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

final readonly class OperatingHoursData
{
    /**
     * @param  array<string, string>  $standardHours
     * @param  list<array<string, mixed>>  $exceptions
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public array $standardHours,
        public array $exceptions,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        $standardHours = (array) ($row['standardHours'] ?? []);
        $exceptions = (array) ($row['exceptions'] ?? []);

        return new self(
            name: (string) ($row['name'] ?? ''),
            description: self::nullableString($row['description'] ?? null),
            standardHours: array_map(static fn (mixed $v): string => (string) $v, $standardHours),
            exceptions: array_values(array_filter(
                $exceptions,
                static fn (mixed $v): bool => is_array($v),
            )),
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
