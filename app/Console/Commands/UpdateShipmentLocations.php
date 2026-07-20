<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shipment;
use App\Services\MarineTrafficService;

class UpdateShipmentLocations extends Command
{
    /**
     * Nama perintah yang akan dipanggil di terminal
     *
     * @var string
     */
    protected $signature = 'logixchain:update-vessels';

    /**
     * Deskripsi singkat perintah di dalam list artisan
     *
     * @var string
     */
    protected $description = 'Sinkronisasi otomatis pergerakan posisi koordinat armada kapal logistik global berbasis waktu nyata';

    protected $marineService;

    /**
     * Inject MarineTrafficService ke dalam Command
     */
    public function __construct(MarineTrafficService $marineService)
    {
        parent::__construct();
        $this->marineService = $marineService;
    }

    /**
     * Eksekusi logika pembaharuan koordinat kapal
     */
    public function handle()
    {
        $this->info('🚀 Memulai pemindaian radar satelit LogixChain...');

        // Ambil semua kapal yang statusnya sedang berlayar
        $activeShipments = Shipment::whereIn('status', ['ON_VOYAGE', 'DEPARTING'])->get();

        if ($activeShipments->isEmpty()) {
            $this->comment('✨ Radar bersih. Tidak ada armada aktif yang sedang berlayar saat ini.');
            return Command::SUCCESS;
        }

        $updatedCount = 0;

        foreach ($activeShipments as $shipment) {
            try {
                // Panggil engine interpolasi linier waktu nyata yang sudah kita buat
                $success = $this->marineService->updateShipmentLocation($shipment);
                
                if ($success) {
                    $updatedCount++;
                    $this->line("✅ Vessel [{$shipment->vessel_name}] berhasil diperbarui ke posisi baru.");
                }
            } catch (\Exception $e) {
                $this->error("❌ Gagal memperbarui posisi kapal TRK-{$shipment->tracking_number}: " . $e->getMessage());
            }
        }

        $this->info("🛰️ Sinkronisasi Selesai! Berhasil memperbarui posisi {$updatedCount} armada kapal.");
        return Command::SUCCESS;
    }
}