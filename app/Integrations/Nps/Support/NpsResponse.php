<?php

declare(strict_types=1);

namespace App\Integrations\Nps\Support;

final readonly class NpsResponse
{
    /** @param list<array<string, mixed>> $data */
    public function __construct(
        public int $total,
        public int $start,
        public int $limit,
        public array $data,
    ) {}

    public function hasMore(): bool
    {
        return $this->start + count($this->data) < $this->total;
    }

    public function nextStart(): int
    {
        return $this->start + count($this->data);
    }
}
