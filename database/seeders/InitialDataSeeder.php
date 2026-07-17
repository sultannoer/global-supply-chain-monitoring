<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil path file ports.json yang ada di folder database
        $jsonPath = database_path('ports.json');
        
        // Proteksi jika filenya tidak sengaja terhapus atau salah nama
        if (!File::exists($jsonPath)) {
            $this->command->error("File ports.json tidak ditemukan di folder database!");
            return;
        }

        // 2. Baca isi file JSON dan ubah menjadi array PHP
        $jsonRaw = File::get($jsonPath);
        $globalData = json_decode($jsonRaw, true);

        // 3. Lakukan perulangan (looping) dengan proteksi data duplikat
        foreach ($globalData as $data) {
            // Menggunakan firstOrCreate agar jika kode negara (PER, dll) sudah ada, tidak akan error duplikat
            $country = Country::firstOrCreate(
                ['code' => $data['code']], // Kolom unik untuk dicek
                [
                    'name' => $data['country'],
                    'currency_code' => $data['curr']
                ]
            );

            // Menggunakan firstOrCreate juga untuk port agar memastikan tidak ada kode port yang duplikat
            Port::firstOrCreate(
                ['port_code' => $data['port_code']], // Kolom unik untuk dicek
                [
                    'country_id' => $country->id,
                    'name' => $data['port'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lon'],
                    'weather_status' => 'Clear',
                    'congestion_level' => rand(5, 50) // Angka kepadatan acak antara 5-50% untuk simulasi
                ]
            );
        }
        
        $this->command->info("Berhasil memasukkan data pelabuhan global!");
    }
}