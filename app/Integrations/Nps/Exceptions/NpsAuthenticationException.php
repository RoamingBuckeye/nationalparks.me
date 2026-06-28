<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Exceptions;

final class NpsAuthenticationException extends NpsException
{
    public static function missingKey(): self
    {
        return new self('NPS API key is not configured. Set NPS_API_KEY in your environment.');
    }

    public static function rejected(string $endpoint): self
    {
        return (new self('NPS API key was rejected.'))
            ->withContext(['endpoint' => $endpoint]);
    }
}
