<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Data;

use App\Integrations\Nps\Enums\ContactKind;

final readonly class ContactData
{
    public function __construct(
        public ContactKind $kind,
        public string $value,
        public ?string $label,
    ) {}

    /**
     * Normalize the NPS shape `{phoneNumbers: [...], emailAddresses: [...]}` into a flat list of contacts.
     *
     * @param  array<string, mixed>|null  $contacts
     * @return list<self>
     */
    public static function listFrom(?array $contacts): array
    {
        if ($contacts === null) {
            return [];
        }

        $out = [];

        foreach ((array) ($contacts['phoneNumbers'] ?? []) as $row) {
            $value = trim((string) ($row['phoneNumber'] ?? ''));
            if ($value === '') {
                continue;
            }
            $out[] = new self(
                kind: ContactKind::Phone,
                value: $value,
                label: self::nullableString($row['type'] ?? null),
            );
        }

        foreach ((array) ($contacts['emailAddresses'] ?? []) as $row) {
            $value = trim((string) ($row['emailAddress'] ?? ''));
            if ($value === '') {
                continue;
            }
            $out[] = new self(
                kind: ContactKind::Email,
                value: $value,
                label: self::nullableString($row['description'] ?? null),
            );
        }

        return $out;
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
