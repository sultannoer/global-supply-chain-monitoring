<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', '');
        $password = (string) env('ADMIN_PASSWORD', '');

        if (app()->environment('production') && ($email === '' || $password === '')) {
            throw new RuntimeException('ADMIN_EMAIL dan ADMIN_PASSWORD wajib diatur sebelum menjalankan seed pada production.');
        }

        // Convenience defaults only for local development. Production must use
        // Railway Variables with a unique, strong administrator password.
        $email = $email !== '' ? $email : 'admin@supplychain.test';
        $password = $password !== '' ? $password : 'Admin123!';

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin Logistik',
                'password' => $password,
                'role' => 'admin',
            ]
        );
    }
}
