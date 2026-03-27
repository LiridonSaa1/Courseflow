<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property string $id
 * @property int|null $plan_id
 * @property \Illuminate\Support\Carbon|null $suspended_at
 * @property string|null $stripe_customer_id
 * @property string|null $stripe_subscription_id
 * @property string|null $subscription_status
 * @property \Illuminate\Support\Carbon|null $subscription_ends_at
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'created_at',
            'updated_at',
            'plan_id',
            'suspended_at',
            'stripe_customer_id',
            'stripe_subscription_id',
            'subscription_status',
            'subscription_ends_at',
            'trial_ends_at',
        ];
    }

    protected function casts(): array
    {
        return [
            'suspended_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function centralOwner(): HasOne
    {
        return $this->hasOne(CentralUser::class, 'tenant_id', 'id');
    }

    public function isActive(): bool
    {
        if ($this->suspended_at !== null) {
            return false;
        }

        if ($this->subscription_ends_at && $this->subscription_ends_at->isPast()) {
            return false;
        }

        if ($this->trial_ends_at && $this->trial_ends_at->isPast() && $this->subscription_status !== 'active') {
            return false;
        }

        return true;
    }
}
