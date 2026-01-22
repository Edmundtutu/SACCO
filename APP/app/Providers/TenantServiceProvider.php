<?php

namespace App\Providers;

use App\Services\TenantContext;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register TenantContext as a singleton
        $this->app->singleton(TenantContext::class, function ($app) {
            return new TenantContext();
        });

        // Load tenant helper functions
        require_once app_path('Helpers/tenant_helpers.php');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
