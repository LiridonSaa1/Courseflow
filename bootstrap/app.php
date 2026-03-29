<?php

use App\Http\Middleware\EnsureCentralSuperAdmin;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\EnsureTenantSessionMatchesContext;
use App\Http\Middleware\EnsureTenantStudentActive;
use App\Http\Middleware\EnsureTenantTeacherActive;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('courseflow:suspend-expired-tenants')->hourly();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        $middleware->redirectGuestsTo(fn () => tenant()
            ? route('tenant.login')
            : route('login'));
        $middleware->alias([
            'tenant.active' => EnsureTenantIsActive::class,
            'tenant.session' => EnsureTenantSessionMatchesContext::class,
            'tenant.student.active' => EnsureTenantStudentActive::class,
            'tenant.teacher.active' => EnsureTenantTeacherActive::class,
            'central.admin' => EnsureCentralSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
