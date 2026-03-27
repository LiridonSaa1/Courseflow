<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant && ! $tenant->isActive()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tenant subscription inactive.'], 402);
            }

            return inertia('Tenant/Suspended', [
                'message' => 'This workspace is suspended or your subscription has expired. Please contact support or renew from the main site.',
            ])->toResponse($request)->setStatusCode(402);
        }

        return $next($request);
    }
}
