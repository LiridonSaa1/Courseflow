<?php

namespace App\Http\Controllers\Central\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class TenantAdminController extends Controller
{
    public function suspend(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update(['suspended_at' => now()]);

        return back();
    }

    public function activate(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update(['suspended_at' => null]);

        return back();
    }
}
