<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureTenantSessionMatchesContext;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Tenant/Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('Invalid credentials.')]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && $user->hasRole('student')) {
            $exists = Student::query()->where('user_id', $user->id)->exists();
            if (! $exists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => __('Your student account is no longer active.'),
                ]);
            }
        }

        if ($user && $user->hasRole('teacher')) {
            $exists = Teacher::query()->where('user_id', $user->id)->exists();
            if (! $exists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => __('Your teacher account is no longer active.'),
                ]);
            }
        }

        $tenant = tenant();
        if ($tenant !== null) {
            $request->session()->put(
                EnsureTenantSessionMatchesContext::SESSION_TENANT_KEY,
                (string) $tenant->getTenantKey(),
            );
        }

        return redirect()->intended(route('tenant.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}
