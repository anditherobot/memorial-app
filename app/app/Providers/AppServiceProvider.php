<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

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
        try {
            if (DB::getDriverName() === 'sqlite') {
                // Avoid write contention errors under light concurrency
                DB::statement('PRAGMA busy_timeout = 5000;');
                DB::statement('PRAGMA foreign_keys = ON;');
            }
        } catch (\Throwable $e) {
            // ignore if DB not ready yet (e.g., during install)
        }
    }
}
