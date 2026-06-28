<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->beforeEach(function (): void {
        // Defense in depth — any feature test that forgets to fake an HTTP
        // call will fail loudly instead of silently hitting a real service.
        Http::preventStrayRequests();
    })
    ->in('Feature');
