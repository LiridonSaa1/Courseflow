<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Solo teachers getting started.',
                'monthly_price_cents' => 1900,
                'yearly_price_cents' => 19000,
                'trial_days' => 14,
                'features' => ['courses' => 3, 'students' => 50],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Growing language schools.',
                'monthly_price_cents' => 4900,
                'yearly_price_cents' => 49000,
                'trial_days' => 14,
                'features' => ['courses' => 25, 'students' => 500],
            ],
            [
                'name' => 'School',
                'slug' => 'school',
                'description' => 'Institutions at scale.',
                'monthly_price_cents' => 14900,
                'yearly_price_cents' => 149000,
                'trial_days' => 14,
                'features' => ['courses' => -1, 'students' => -1],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
