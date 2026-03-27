<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Controllers\TenantAssetsController;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

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
        // Default is InitializeTenancyByDomain (full host), but domains.domain stores only the
        // subdomain label. Use subdomain middleware so /tenancy/assets/* resolves the tenant.
        TenantAssetsController::$tenancyMiddleware = InitializeTenancyBySubdomain::class;
    }
}
