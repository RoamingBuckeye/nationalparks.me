<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Support;

use App\Integrations\Nps\Exceptions\NpsAuthenticationException;

final readonly class NpsConfig
{
    public function __construct(
        public string $apiKey,
        public string $baseUrl,
        public int $timeout,
        public int $connectTimeout,
        public int $retries,
        public int $retryDelayMs,
        public int $pageSize,
    ) {
        if ($apiKey === '') {
            throw NpsAuthenticationException::missingKey();
        }
    }

    public static function fromConfig(): self
    {
        return new self(
            apiKey: (string) config('services.nps.key'),
            baseUrl: rtrim((string) config('services.nps.base_url', 'https://developer.nps.gov/api/v1/'), '/').'/',
            timeout: (int) config('services.nps.timeout', 15),
            connectTimeout: (int) config('services.nps.connect_timeout', 5),
            retries: (int) config('services.nps.retries', 2),
            retryDelayMs: (int) config('services.nps.retry_delay_ms', 250),
            pageSize: (int) config('services.nps.page_size', 200),
        );
    }
}
