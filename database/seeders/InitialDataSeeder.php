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
       
        $jsonPath = database_path('ports.json');
        
        
        if (!File::exists($jsonPath)) {
            $this->command->error("File ports.json tidak ditemukan di folder database!");
            return;
        }

        
        $jsonRaw = File::get($jsonPath);
        $globalData = json_decode($jsonRaw, true);

        
        foreach ($globalData as $data) {
            
            $country = Country::firstOrCreate(
                ['code' => $data['code']], 
                [
                    'name' => $data['country'],
                    'currency_code' => $data['curr']
                ]
            );

           
            Port::firstOrCreate(
                ['port_code' => $data['port_code']], 
                [
                    'country_id' => $country->id,
                    'name' => $data['port'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lon'],
                    'weather_status' => 'Clear',
                    'congestion_level' => rand(5, 50) 
                ]
            );
        }
        
        $this->command->info("Berhasil memasukkan data pelabuhan global!");
    }
}