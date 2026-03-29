<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    use AuthorizesTenantRoles;

    public function __invoke(Request $request): Response
    {
        $this->tenantAdmin($request);

        return Inertia::render('Tenant/Settings/Index');
    }
}
