<?php

namespace Database\Seeders;

use Database\Seeders\Tenant\LmsDemoSeeder;
use Illuminate\Database\Seeder;

/**
 * Entry seeder for tenant databases (see config/tenancy.php tenants:seed).
 */
class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LmsDemoSeeder::class);
    }
}
