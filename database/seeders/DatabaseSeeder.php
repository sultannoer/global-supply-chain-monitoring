<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Memanggil seeder negara dan pelabuhan yang sudah kita buat tadi
        $this->call([
            InitialDataSeeder::class,
        ]);
    }
}
