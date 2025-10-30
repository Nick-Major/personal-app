<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\WorkRequest; // ← ДОБАВИТЬ ЭТУ СТРОКУ
use App\Observers\WorkRequestObserver; // ← И ЭТУ СТРОКУ

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
        WorkRequest::observe(WorkRequestObserver::class); // ← ИСПРАВИТЬ ЭТУ СТРОКУ
    }
}
