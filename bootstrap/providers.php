<?php

use App\Providers\AppServiceProvider;
use App\Providers\AutoHelperModelProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    AutoHelperModelProvider::class,
    AdminPanelProvider::class,
];
