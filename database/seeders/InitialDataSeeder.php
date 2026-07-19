<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Country;
use App\Models\Port;
use App\Models\Shipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        
        User::factory()->create([
            'name' => 'Admin Logistik',
            'email' => 'admin@supplychain.com',
        ]);

       
        $jsonPath = database_path('ports.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->error("File ports.json tidak ditemukan di folder database!");
            return;
        }

        $jsonRaw = File::get($jsonPath);
        $globalData = json_decode($jsonRaw, true);

        $this->command->info("Sedang memproses data maritim global...");

        foreach ($globalData as $data) {
            $countryCode = strtoupper(trim($data['code']));
            $countryName = $data['country'] ?? ($data['raw_country'] ?? 'Unknown Country');

         
            $country = Country::firstOrCreate(
                ['code' => $countryCode], 
                [
                    'name' => $countryName,
                    'region' => $this->guessRegion($countryCode),
                    'currency_code' => $data['curr'] ?? 'USD',
                    'language' => 'Official Language'
                ]
            );

           
            Port::firstOrCreate(
                ['name' => $data['port']], 
                [
                    'country_code' => $country->code,
                    'latitude' => (float)$data['lat'],
                    'longitude' => (float)$data['lon'],
                ]
            );
        }
        
        $this->command->info("Berhasil memasukkan data pelabuhan global!");

        $idnPort = Port::where('country_code', 'IDN')->first();
        $nldPort = Port::where('country_code', 'NLD')->first();
        $usaPort = Port::where('country_code', 'USA')->first();
        $chnPort = Port::where('country_code', 'CHN')->first();

        if ($idnPort && $nldPort && $usaPort && $chnPort) {
            $this->command->info("Membuat simulasi rute pelayaran aktif untuk peta...");
            
            $shipments = [
                [
                    'tracking_number' => 'GSC-' . strtoupper(Str::random(8)),
                    'vessel_name' => 'MV Nusantara Voyager',
                    'origin_port_id' => $idnPort->id,
                    'destination_port_id' => $nldPort->id,
                    'current_lat' => $idnPort->latitude,
                    'current_lng' => $idnPort->longitude,
                    'departure_date' => now()->subDays(5),
                    'baseline_eta' => now()->addDays(15),
                    'adaptive_eta' => now()->addDays(15),
                    'initial_cost_usd' => 450000.00,
                    'current_exchange_rate' => 1.00,
                    'status' => 'ON_VOYAGE',
                    'risk_score' => 0
                ],
                [
                    'tracking_number' => 'GSC-' . strtoupper(Str::random(8)),
                    'vessel_name' => 'Pacific Express II',
                    'origin_port_id' => $chnPort->id,
                    'destination_port_id' => $usaPort->id,
                    'current_lat' => $chnPort->latitude,
                    'current_lng' => $chnPort->longitude,
                    'departure_date' => now()->subDays(2),
                    'baseline_eta' => now()->addDays(10),
                    'adaptive_eta' => now()->addDays(12),
                    'initial_cost_usd' => 620000.00,
                    'current_exchange_rate' => 1.00,
                    'status' => 'ON_VOYAGE',
                    'risk_score' => 0
                ]
            ];

            foreach ($shipments as $s) {
                Shipment::create($s);
            }
            $this->command->info("Simulasi pelayaran kapal siap meluncur!");
        }
    }

   
     
    private function guessRegion(string $code): string
    {
        $code = strtoupper(trim($code));

        $regions = [
            'Asia' => ['IDN', 'CHN', 'JPN', 'SGP', 'KOR', 'IND', 'ARE', 'SAU', 'MYS', 'THA', 'VNM', 'PHL', 'TWN', 'TUR', 'PAK', 'LKA', 'BGD', 'MMR', 'KHM', 'HKG', 'KWT', 'QAT', 'OMN', 'BHR', 'IRN', 'IRQ', 'YEM', 'JOR', 'LBN', 'SYR', 'MDV', 'BRN', 'TLS', 'ISR'],
            'Europe' => ['NLD', 'DEU', 'GBR', 'FRA', 'BEL', 'RUS', 'ITA', 'ESP', 'GRC', 'IRL', 'PRT', 'SWE', 'NOR', 'DNK', 'FIN', 'POL', 'UKR', 'CYP', 'MLT', 'ISL', 'EST', 'LVA', 'LTU', 'HRV', 'SVN', 'MNE', 'ALB', 'ROU', 'BGR', 'GEO'],
            'Americas' => ['USA', 'BRA', 'MEX', 'CAN', 'ARG', 'CHL', 'PER', 'COL', 'PAN', 'ECU', 'VEN', 'SUR', 'GUY', 'CRI', 'NIC', 'HND', 'SLV', 'GTM', 'CUB', 'JAM', 'DOM', 'HTI', 'TTO', 'URY'],
            'Africa' => ['ZAF', 'EGY', 'TUN', 'DZA', 'LBY', 'SDN', 'DJI', 'SOM', 'TAN', 'MOZ', 'MDG', 'MUS', 'AGO', 'COD', 'COG', 'GAB', 'CMR', 'BEN', 'TGO', 'CIV', 'LBR', 'SLE', 'GIN', 'SEN', 'MRT', 'GMB', 'GNB', 'GNQ', 'NAM', 'ERI', 'UGA', 'GHA', 'KEN', 'MAR', 'MUS'],
            'Oceania' => ['AUS', 'NZL', 'PNG', 'FJI', 'SLB', 'VUT', 'NCL']
        ];

        foreach ($regions as $region => $codes) {
            if (in_array($code, $codes)) {
                return $region;
            }
        }

        return 'Global';
    }
}