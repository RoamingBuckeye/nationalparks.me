<?php

declare(strict_types=1);

namespace App\Actions\Photos;

use Illuminate\Support\Carbon;
use Throwable;

class ExtractPhotoMetadata
{
    /**
     * Pull EXIF capture date and GPS coordinates from an image, if present.
     * Returns nulls for anything missing or when EXIF can't be read.
     *
     * @return array{taken_at: ?Carbon, latitude: ?float, longitude: ?float}
     */
    public function __invoke(string $absolutePath, ?string $mime): array
    {
        $empty = ['taken_at' => null, 'latitude' => null, 'longitude' => null];

        if (! function_exists('exif_read_data')) {
            return $empty;
        }

        if (! in_array($mime, ['image/jpeg', 'image/tiff'], true)) {
            return $empty;
        }

        try {
            $exif = @exif_read_data($absolutePath);
        } catch (Throwable) {
            return $empty;
        }

        if (! is_array($exif)) {
            return $empty;
        }

        return [
            'taken_at' => $this->takenAt($exif),
            'latitude' => $this->coordinate($exif, 'GPSLatitude', 'GPSLatitudeRef', 'S'),
            'longitude' => $this->coordinate($exif, 'GPSLongitude', 'GPSLongitudeRef', 'W'),
        ];
    }

    /**
     * @param  array<string, mixed>  $exif
     */
    protected function takenAt(array $exif): ?Carbon
    {
        $raw = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? null;

        if (! is_string($raw)) {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('Y:m:d H:i:s', $raw);
        } catch (Throwable) {
            return null;
        }

        return $parsed ?: null;
    }

    /**
     * @param  array<string, mixed>  $exif
     */
    protected function coordinate(array $exif, string $key, string $refKey, string $negativeRef): ?float
    {
        if (! isset($exif[$key], $exif[$refKey]) || ! is_array($exif[$key]) || count($exif[$key]) < 3) {
            return null;
        }

        [$degrees, $minutes, $seconds] = array_map(
            fn (mixed $part): float => $this->rational((string) $part),
            array_values($exif[$key]),
        );

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if (strtoupper((string) $exif[$refKey]) === $negativeRef) {
            $decimal = -$decimal;
        }

        return round($decimal, 7);
    }

    /**
     * Evaluate an EXIF rational ("num/den") as a float.
     */
    protected function rational(string $value): float
    {
        if (! str_contains($value, '/')) {
            return (float) $value;
        }

        [$numerator, $denominator] = explode('/', $value, 2);

        return (float) $denominator === 0.0 ? 0.0 : (float) $numerator / (float) $denominator;
    }
}
