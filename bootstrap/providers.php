<?php

use App\Providers\AppServiceProvider;
use App\Providers\AutoHelperModelProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\LogViewerProvider;

return [
    AppServiceProvider::class,
    AutoHelperModelProvider::class,
    AdminPanelProvider::class,
    LogViewerProvider::class,
];
