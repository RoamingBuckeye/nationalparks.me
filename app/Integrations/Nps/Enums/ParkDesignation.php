<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Enums;

/**
 * The four NPS designations that, together with a small parkCode allowlist,
 * cover the canonical "63 National Parks" list. `None` represents NPS units
 * with an empty designation field (American Samoa is the only canonical one).
 *
 * Non-canonical designations (National Monument, National Recreation Area,
 * etc.) are not enumerated — `tryFrom` returns null and the canonical filter
 * rejects them.
 */
enum ParkDesignation: string
{
    case NationalPark = 'National Park';
    case NationalParkAndPreserve = 'National Park & Preserve';
    case NationalParks = 'National Parks';
    case NationalAndStateParks = 'National and State Parks';
    case None = '';

    public function isCanonical(): bool
    {
        return match ($this) {
            self::NationalPark,
            self::NationalParkAndPreserve,
            self::NationalAndStateParks => true,
            self::NationalParks => true, // seki — gets split into sequ+kica downstream
            self::None => false, // npsa qualifies via the park-code allowlist, not designation
        };
    }
}
