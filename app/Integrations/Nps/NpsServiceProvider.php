<?php

declare(strict_types=1);

namespace App\Integrations\Nps;

use App\Integrations\Nps\Contracts\NpsClient;
use App\Integrations\Nps\Http\HttpNpsClient;
use App\Integrations\Nps\Support\NpsConfig;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

final class NpsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NpsConfig::class, static fn (): NpsConfig => NpsConfig::fromConfig());

        $this->app->singleton(NpsClient::class, static fn ($app): HttpNpsClient => new HttpNpsClient(
            $app->make(HttpFactory::class),
            $app->make(NpsConfig::class),
        ));
    }
}
