<?php

namespace App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // public const HOME = '/inicio'; // ← CAMBIA AQUÍ
    
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

        // Route::middleware('web')   ->group(base_path('routes/web.php'));
    }
}
