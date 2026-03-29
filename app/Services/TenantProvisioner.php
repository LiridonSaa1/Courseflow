<?php

namespace App\Services;

use App\Models\CentralUser;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TenantProvisioner
{
    public function __construct(
        protected SubdomainAllocator $subdomains
    ) {}

    /**
     * @param  array<string, mixed>  $data  Expects course_name; optional subdomain override (nullable string).
     */
    public function provision(array $data): Tenant
    {
        // Do not wrap in DB::transaction() on MySQL: Tenant::create() dispatches Stancl CreateDatabase,
        // and CREATE DATABASE causes an implicit commit, so Laravel's closing commit() throws
        // "There is no active transaction".

        $plan = Plan::query()->findOrFail($data['plan_id']);
        $override = isset($data['subdomain']) && is_string($data['subdomain']) && trim($data['subdomain']) !== ''
            ? $data['subdomain']
            : null;

        $subdomain = $this->subdomains->allocate((string) $data['course_name'], $override);

        $centralUser = CentralUser::query()->create([
            'name' => $data['teacher_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
        ]);

        $trialDays = (int) $plan->trial_days;
        $tenant = Tenant::query()->create([
            'plan_id' => $plan->id,
            'trial_ends_at' => now()->addDays($trialDays),
            'subscription_status' => 'trialing',
            'course_name' => $data['course_name'],
            'language' => $data['language'],
            'tenant_type' => $data['tenant_type'],
        ]);

        $tenant->createDomain($subdomain);

        $centralUser->update(['tenant_id' => $tenant->id]);

        $tenant->run(function () use ($data) {
            foreach (['owner', 'admin', 'teacher', 'student'] as $role) {
                Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            }

            $owner = User::query()->create([
                'name' => $data['teacher_name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            $owner->assignRole('owner');
        });

        return $tenant->fresh();
    }
}
