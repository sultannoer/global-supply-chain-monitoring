<?php

namespace App\Console\Commands;

use App\Models\Port;
use App\Models\Shipment;
use App\Services\RouteWeatherEtaService;
use App\Services\VoyageCostEstimator;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillShipmentEtas extends Command
{
    protected $signature = 'shipments:backfill-eta {--force : Recalculate all shipments, including rows that already have ETA}';

    protected $description = 'Fill legacy shipment baseline/adaptive ETA from deterministic route and vessel estimates';

    public function handle(VoyageCostEstimator $estimator, RouteWeatherEtaService $routeWeather): int
    {
        $query = Shipment::with(['originPort', 'destinationPort'])->orderBy('id');
        if (! $this->option('force')) {
            $query->where(function ($builder) {
                $builder->whereNull('departure_date')
                    ->orWhereNull('baseline_eta')
                    ->orWhereNull('adaptive_eta');
            });
        }

        $shipments = $query->get();
        $stormZones = Port::query()
            ->whereIn('storm_risk_status', ['High', 'Medium'])
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->get(['name', 'latitude', 'longitude', 'storm_risk_status'])
            ->map(fn (Port $port) => [
                'name' => $port->name,
                'lat' => (float) $port->latitude,
                'lng' => (float) $port->longitude,
                'radius_km' => $port->storm_risk_status === 'High' ? 300 : 180,
                'risk' => strtoupper($port->storm_risk_status),
            ])->all();

        $this->info("Backfilling ETA for {$shipments->count()} legacy shipment(s)...");
        $bar = $this->output->createProgressBar($shipments->count());
        foreach ($shipments as $shipment) {
            $estimate = $estimator->estimate(
                ['lat' => $shipment->originPort?->latitude, 'lng' => $shipment->originPort?->longitude, 'storm_risk_status' => $shipment->originPort?->storm_risk_status],
                ['lat' => $shipment->destinationPort?->latitude, 'lng' => $shipment->destinationPort?->longitude, 'storm_risk_status' => $shipment->destinationPort?->storm_risk_status],
                (float) $shipment->cargo_weight,
                (float) $shipment->initial_cost_usd,
                (string) $shipment->vessel_name,
            );
            if (! $estimate['ready']) {
                $this->warn("\nShipment #{$shipment->id} dilewati: koordinat rute tidak lengkap.");
                $bar->advance();
                continue;
            }

            $departure = $shipment->departure_date
                ? Carbon::parse($shipment->departure_date)->startOfDay()
                : Carbon::parse($shipment->created_at)->startOfDay();
            $baseline = $departure->copy()->addHours((int) round($estimate['transit_days'] * 24));
            $shipment->fill([
                'departure_date' => $departure->toDateString(),
                'baseline_eta' => $baseline->toDateString(),
                'adaptive_eta' => $baseline->toDateString(),
            ])->save();
            $shipment->refresh()->load(['originPort', 'destinationPort']);
            $routeWeather->synchronize($shipment, $stormZones);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('ETA backfill selesai. Baseline memakai jarak rute + profil kapal; ETA adaptif memakai zona badai aktif.');

        return self::SUCCESS;
    }
}
