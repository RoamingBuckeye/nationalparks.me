<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;

final class NpsRateLimitedException extends NpsException implements ShouldntReport
{
    public function __construct(
        public readonly int $retryAfterSeconds,
        string $endpoint,
    ) {
        parent::__construct("NPS API rate limit exceeded; retry after {$retryAfterSeconds}s.");
        $this->withContext([
            'endpoint' => $endpoint,
            'retry_after_seconds' => $retryAfterSeconds,
        ]);
    }
}
