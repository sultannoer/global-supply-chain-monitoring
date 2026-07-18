<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shipment;
use App\Models\Port;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $port = Port::find(1) ?? Port::first();

        if ($port) {
            Shipment::create([
                'shipment_number' => 'MSCU9823410',
                'origin_port_id' => 2, 
                'destination_port_id' => $port->id,
                'departure_date' => now()->subDays(5)->toDateTimeString(),
                'estimated_arrival_date' => now()->addDays(5)->toDateTimeString(),
                'initial_cost' => 5000.00,
                'risk_status' => 'LOW',
                'risk_reason' => 'Rute pelayaran aman dan cuaca cerah.'
            ]);

            Shipment::create([
                'shipment_number' => 'CMAQ4451290',
                'origin_port_id' => 3,
                'destination_port_id' => $port->id,
                'departure_date' => now()->subDays(2)->toDateTimeString(),
                'estimated_arrival_date' => now()->addDays(2)->toDateTimeString(),
                'initial_cost' => 7500.00,
                'risk_status' => 'HIGH',
                'risk_reason' => 'Kemacetan parah di pelabuhan tujuan transit.'
            ]);
        }
    }
}