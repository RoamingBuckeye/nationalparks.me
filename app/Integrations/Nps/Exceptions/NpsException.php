<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Exceptions;

use RuntimeException;

abstract class NpsException extends RuntimeException
{
    /** @var array<string, mixed> */
    protected array $contextData = [];

    /** @param array<string, mixed> $context */
    public function withContext(array $context): static
    {
        $this->contextData = [...$this->contextData, ...$context];

        return $this;
    }

    /** @return array<string, mixed> */
    public function context(): array
    {
        return $this->contextData;
    }
}
