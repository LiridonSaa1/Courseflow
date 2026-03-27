<?php

use App\Http\Controllers\Central\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Central\Admin\TenantAdminController;
use App\Http\Controllers\Central\AuthenticatedSessionController;
use App\Http\Controllers\Central\HomeController;
use App\Http\Controllers\Central\MarketingController;
use App\Http\Controllers\Central\SubscribeController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::get('/', HomeController::class)->name('home');
Route::get('/pricing', [MarketingController::class, 'pricing'])->name('pricing');
Route::get('/features', [MarketingController::class, 'features'])->name('features');
Route::get('/contact', [MarketingController::class, 'contact'])->name('contact');

Route::prefix('admin')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::get('/subscribe', [SubscribeController::class, 'create'])->name('subscribe');
Route::post('/subscribe', [SubscribeController::class, 'store'])->name('subscribe.store');

Route::middleware(['auth:central'])->group(function () {
    Route::post('admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('central.admin')->group(function () {
        /*
         * Tenant routes also register GET /dashboard and load after web.php, which would override
         * a central /dashboard. Bind the admin dashboard only to central domains so courseflow.test/dashboard
         * shows the admin panel while {tenant}.courseflow.test/dashboard stays the LMS.
         */
        $centralHosts = array_values(array_unique(array_filter(config('tenancy.central_domains'))));
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (is_string($appHost) && $appHost !== '' && in_array($appHost, $centralHosts, true)) {
            usort($centralHosts, fn (string $a, string $b): int => $a === $appHost ? -1 : ($b === $appHost ? 1 : 0));
        }
        $nameAdminDashboard = true;
        foreach ($centralHosts as $centralHost) {
            Route::domain($centralHost)->group(function () use (&$nameAdminDashboard) {
                $route = Route::get('dashboard', AdminDashboardController::class);
                if ($nameAdminDashboard) {
                    $route->name('admin.dashboard');
                    $nameAdminDashboard = false;
                }
            });
        }

        Route::prefix('admin')->group(function () {
            Route::get('/', AdminDashboardController::class)->name('admin.home');
            Route::post('tenants/{tenant}/suspend', [TenantAdminController::class, 'suspend'])->name('admin.tenants.suspend');
            Route::post('tenants/{tenant}/activate', [TenantAdminController::class, 'activate'])->name('admin.tenants.activate');
        });
    });
});
