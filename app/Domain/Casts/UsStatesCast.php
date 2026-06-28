<?php

declare(strict_types=1);

namespace App\Domain\Casts;

use App\Domain\UsState;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom cast for a JSON array of US state codes — reads into a list<UsState>,
 * writes back as a JSON list of the case values. Unknown codes are dropped
 * on read (Eloquent's built-in array cast can't do enum elements).
 *
 * @implements CastsAttributes<list<UsState>, iterable<UsState|string>>
 */
class UsStatesCast implements CastsAttributes
{
    /** @return list<UsState> */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (! is_array($decoded)) {
            return [];
        }

        $states = [];
        foreach ($decoded as $code) {
            $state = UsState::tryFrom((string) $code);
            if ($state !== null) {
                $states[] = $state;
            }
        }

        return $states;
    }

    /**
     * @param  iterable<UsState|string>|null  $value
     * @return array<string, string|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        $codes = [];
        foreach ($value as $item) {
            if ($item instanceof UsState) {
                $codes[] = $item->value;
            } elseif (UsState::tryFrom($item) !== null) {
                $codes[] = $item;
            }
        }

        $encoded = json_encode($codes);

        return [$key => $encoded === false ? null : $encoded];
    }
}
