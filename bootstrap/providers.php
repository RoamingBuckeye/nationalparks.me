<?php

use App\Integrations\Nps\NpsServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    NpsServiceProvider::class,
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    HorizonServiceProvider::class,
];
