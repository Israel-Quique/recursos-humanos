<?php

namespace App\Providers;

use App\Services\BiometricoAutoSyncService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        app(BiometricoAutoSyncService::class)->triggerIfDue();
    }
}
