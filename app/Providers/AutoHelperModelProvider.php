<?php

namespace App\Providers;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AutoHelperModelProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (app()->isLocal() && config('app.enable_helper_model')) {
            Event::listen(MigrationsEnded::class, function (MigrationsEnded $event) {
                Artisan::call('ide-helper:models -R -W');
            });
        }
    }
}
