<?php

use App\Providers\AppServiceProvider;
use App\Providers\AutoHelperModelProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\LogViewerProvider;
use App\Providers\PulseServiceProvider;
use App\Providers\ServiceProvider;

return [
    AppServiceProvider::class,
    AutoHelperModelProvider::class,
    AdminPanelProvider::class,
    LogViewerProvider::class,
    PulseServiceProvider::class,
    ServiceProvider::class,
];
