<?php

namespace App\Http\Middleware;

use App\Models\Teacher;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantTeacherActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('teacher')) {
            $exists = Teacher::query()->where('user_id', $user->id)->exists();
            if (! $exists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('tenant.login')->withErrors([
                    'email' => __('Your teacher account is no longer active.'),
                ]);
            }
        }

        return $next($request);
    }
}
