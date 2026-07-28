<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@supplychain.com')],
            [
                'name' => 'Admin Logistik',
                'password' => env('ADMIN_PASSWORD', 'Admin123!'),
                'role' => 'admin',
            ]
        );
    }
}
