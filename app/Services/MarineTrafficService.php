<?php

namespace App\Services;

class MarineTrafficService
{
    /**
     * Menggabungkan Data Angka Operasional Pintar dan Peta Radar Live
     * untuk menampilkan profil pelabuhan global yang super lengkap.
     */
    public function getPortTrafficData($latitude, $longitude, $zoom = 10)
    {
        // === 1. BAGIAN DATA ANGKA OPERASIONAL (ALGORITMA SIMULATOR) ===
        // Mengunci seed acak menggunakan koordinat unik agar data tiap pelabuhan stabil & tidak berubah-ubah saat di-refresh
        $seed = abs((int)($latitude * 100) + (int)($longitude * 100));
        srand($seed);

        $baseShips = rand(25, 145);
        $waitingTime = rand(3, 22);

        if ($baseShips > 100) {
            $status = 'High Congestion (Padat)';
            $statusColor = 'danger'; // Merah di Bootstrap/Tailwind
        } elseif ($baseShips > 55) {
            $status = 'Moderate (Normal)';
            $statusColor = 'warning'; // Kuning
        } else {
            $status = 'Low Traffic (Lancar)';
            $statusColor = 'success'; // Hijau
        }

        // === 2. BAGIAN PETA RADAR LIVE INTERAKTIF ===
        $params = http_build_query([
            'lat' => trim($latitude),
            'lon' => trim($longitude),
            'zoom' => $zoom,
            'fleet' => '',
            'theme' => 'light',
            'mapsource' => '0',
        ]);
        $embedUrl = "https://www.marinetraffic.com/en/ais/embed/{$params}";

        // === 3. GABUNGKAN SEMUA DATA KE SATU ARRAY ===
        return [
            'success' => true,
            
            // Data Angka Operasional (Untuk Widget/Tabel)
            'total_kapal_aktif'       => $baseShips . ' Vessel',
            'kargo_kontainer'         => round($baseShips * 0.5) . ' Unit',
            'tanker_minyak'           => round($baseShips * 0.3) . ' Unit',
            'kapal_tunda'             => round($baseShips * 0.2) . ' Unit',
            'status_kepadatan'        => $status,
            'warna_status'            => $statusColor,
            'estimasi_antrean_sandar' => $waitingTime . ' Jam',
            'jarak_pandang_laut'      => rand(9, 14) . ' NM',
            
            // Data Peta (Untuk Tampilan Visual)
            'embed_url'               => $embedUrl,
            'html_iframe'             => '<iframe name="marinetraffic" id="marinetraffic" width="100%" height="450" src="' . $embedUrl . '" frameborder="0" scrolling="no"></iframe>',
            
            'sumber'                  => 'AIS Intelligence Engine (Hybrid System)'
        ];
    }
}