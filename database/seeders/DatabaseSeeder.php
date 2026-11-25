<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account (override with ADMIN_EMAIL / ADMIN_PASSWORD in .env)
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@abdumanage.test')],
            [
                'name' => 'System Admin',
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        // Demo data is great for a presentation. Set SEED_DEMO=false to skip it.
        if (filter_var(env('SEED_DEMO', true), FILTER_VALIDATE_BOOL)) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
