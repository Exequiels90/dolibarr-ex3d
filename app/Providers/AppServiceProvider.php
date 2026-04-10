<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use App\Models\WorkQueue;
use App\Observers\WorkQueueObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register WorkQueue Observer
        WorkQueue::observe(WorkQueueObserver::class);

        // Force HTTPS scheme for production deployment on Render.com
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Fix for older MySQL versions if needed
        if (config('database.default') === 'mysql') {
            Schema::defaultStringLength(191);
        }
    }
}
