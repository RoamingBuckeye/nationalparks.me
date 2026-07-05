<?php

use App\Integrations\Nps\NpsServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    NpsServiceProvider::class,
    AppServiceProvider::class,
    FortifyServiceProvider::class,
];
