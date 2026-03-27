<?php

namespace App\Http\Controllers\Central\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Tenant;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $revenueCents = (int) Payment::query()->where('status', 'succeeded')->sum('amount_cents');

        $chartSignups = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $chartSignups[] = Tenant::query()->whereDate('created_at', $day)->count();
        }

        return Inertia::render('Central/Admin/Dashboard', [
            'stats' => [
                'tenants' => Tenant::query()->count(),
                'activeTenants' => Tenant::query()->whereNull('suspended_at')->count(),
                'revenue_cents' => $revenueCents,
            ],
            'chartSignups' => $chartSignups,
            'tenants' => Tenant::query()->with(['domains', 'plan', 'centralOwner'])->latest()->take(50)->get(),
        ]);
    }
}
