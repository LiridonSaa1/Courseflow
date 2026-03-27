<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Plan extends Model
{
    use CentralConnection;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price_cents',
        'yearly_price_cents',
        'stripe_price_monthly_id',
        'stripe_price_yearly_id',
        'features',
        'trial_days',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'trial_days' => 'integer',
            'monthly_price_cents' => 'integer',
            'yearly_price_cents' => 'integer',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
