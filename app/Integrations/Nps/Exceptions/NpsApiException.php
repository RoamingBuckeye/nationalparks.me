<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Exceptions;

final class NpsApiException extends NpsException
{
    public static function fromResponse(int $status, string $endpoint, string $body): self
    {
        $exception = new self("NPS API returned HTTP {$status} for {$endpoint}.");

        return $exception->withContext([
            'status' => $status,
            'endpoint' => $endpoint,
            'body' => mb_substr($body, 0, 500),
        ]);
    }
}
