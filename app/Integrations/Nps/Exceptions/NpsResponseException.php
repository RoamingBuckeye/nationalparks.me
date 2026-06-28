<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Exceptions;

final class NpsResponseException extends NpsException
{
    public static function missingKey(string $key, string $endpoint): self
    {
        return (new self("NPS response from {$endpoint} is missing expected key '{$key}'."))
            ->withContext(['endpoint' => $endpoint, 'missing_key' => $key]);
    }

    public static function unexpectedShape(string $endpoint, string $reason): self
    {
        return (new self("NPS response from {$endpoint} has unexpected shape: {$reason}"))
            ->withContext(['endpoint' => $endpoint, 'reason' => $reason]);
    }
}
