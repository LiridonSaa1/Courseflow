<?php

namespace Database\Seeders;

use App\Models\CentralUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(PlanSeeder::class);

        CentralUser::query()->updateOrCreate(
            ['email' => 'admin@courseflow.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'is_super_admin' => true,
            ]
        );
    }
}
