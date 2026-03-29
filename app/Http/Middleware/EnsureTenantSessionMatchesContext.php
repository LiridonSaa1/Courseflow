<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the session to the tenant resolved from the domain/subdomain.
 * Prevents reusing a session issued on one tenant workspace on another tenant host.
 */
class EnsureTenantSessionMatchesContext
{
    public const SESSION_TENANT_KEY = 'tenant_workspace_id';

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant === null) {
            return $next($request);
        }

        $currentKey = (string) $tenant->getTenantKey();

        if (! $request->user()) {
            return $next($request);
        }

        $stored = $request->session()->get(self::SESSION_TENANT_KEY);

        if ($stored === null) {
            $request->session()->put(self::SESSION_TENANT_KEY, $currentKey);

            return $next($request);
        }

        if ((string) $stored !== $currentKey) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('tenant.login')
                ->withErrors([
                    'email' => __('Your session belongs to a different workspace. Please sign in again.'),
                ]);
        }

        return $next($request);
    }
}
