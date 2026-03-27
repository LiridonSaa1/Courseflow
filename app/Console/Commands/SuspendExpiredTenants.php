<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class SuspendExpiredTenants extends Command
{
    protected $signature = 'courseflow:suspend-expired-tenants';

    protected $description = 'Suspend tenants past subscription end (and expired trials without active status).';

    public function handle(): int
    {
        $count = Tenant::query()
            ->whereNull('suspended_at')
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('subscription_ends_at')
                        ->where('subscription_ends_at', '<', now());
                })->orWhere(function ($q3) {
                    $q3->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '<', now())
                        ->whereNotIn('subscription_status', ['active', 'trialing']);
                });
            })
            ->update(['suspended_at' => now()]);

        $this->info('Suspended tenants: '.$count);

        return self::SUCCESS;
    }
}
