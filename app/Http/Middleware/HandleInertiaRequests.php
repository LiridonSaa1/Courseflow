<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $this->sharedUser($request),
            ],
            'tenantContext' => fn () => tenant() ? [
                'id' => tenant('id'),
                'workspace' => tenant('course_name') ?? tenant('id'),
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function sharedUser(Request $request): ?array
    {
        if (tenant()) {
            $u = $request->user();
            if (! $u) {
                return null;
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'roles' => $u->getRoleNames()->values()->all(),
            ];
        }

        $u = $request->user('central');
        if (! $u) {
            return null;
        }

        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'is_super_admin' => (bool) $u->is_super_admin,
        ];
    }
}
