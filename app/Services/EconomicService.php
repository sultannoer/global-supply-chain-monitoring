<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EconomicService
{
    // URL dasar API World Bank v2
    protected $baseUrl = 'https://api.worldbank.org/v2/country';

    /**
     * Mengambil data makroekonomi lengkap dari World Bank berdasarkan kode negara (2 huruf)
     */
    public function getEconomicData($countryCode)
    {
        try {
            // Kita ambil data untuk indikator GDP, Inflasi, Populasi, Ekspor, dan Impor
            return [
                'success'   => true,
                'gdp'       => $this->fetchIndicator($countryCode, 'NY.GDP.MKTP.CD'),
                'inflasi'   => $this->fetchIndicator($countryCode, 'FP.CPI.TOTL.ZG'),
                'populasi'  => $this->fetchIndicator($countryCode, 'SP.POP.TOTL'),
                'ekspor'    => $this->fetchIndicator($countryCode, 'NE.EXP.GNFS.ZS'),
                'impor'     => $this->fetchIndicator($countryCode, 'NE.IMP.GNFS.ZS'),
            ];
        } catch (\Exception $e) {
            Log::error('World Bank Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memuat data ekonomi dari World Bank.'
            ];
        }
    }

    /**
     * Helper internal untuk menembak satu indikator spesifik
     */
    private function fetchIndicator($countryCode, $indicatorCode)
    {
        // Format URL World Bank: /country/{country}/indicator/{indicator}?format=json&per_page=1
        // per_page=1 diambil agar kita langsung dapat data tahun terbaru yang tersedia
        $response = Http::get("{$this->baseUrl}/{$countryCode}/indicator/{$indicatorCode}", [
            'format'   => 'json',
            'per_page' => 1
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // World Bank mengembalikan response array di mana indeks [1] adalah baris datanya
            if (isset($data[1]) && count($data[1]) > 0) {
                $latestData = $data[1][0];
                $value = $latestData['value'];
                $year = $latestData['date'];

                if ($value === null) {
                    return 'Data Belum Tersedia';
                }

                // Format tampilan agar lebih rapi dibaca manusia
                return $this->formatOutput($indicatorCode, $value) . " ({$year})";
            }
        }

        return 'N/A';
    }

    /**
     * Merapikan format angka output sesuai jenis indikatornya
     */
    private function formatOutput($indicatorCode, $value)
    {
        switch ($indicatorCode) {
            case 'NY.GDP.MKTP.CD':
                // Mengubah USD mentah menjadi format Billion (Miliar) atau Trillion (Triliun) USD
                if ($value >= 1000000000000) {
                    return '$' . number_format($value / 1000000000000, 2) . ' Triliun';
                }
                return '$' . number_format($value / 1000000000, 2) . ' Miliar';
                
            case 'SP.POP.TOTL':
                // Format jumlah penduduk pakai titik pemisah ribuan
                return number_format($value, 0, ',', '.') . ' Jiwa';
                
            case 'FP.CPI.TOTL.ZG':
            case 'NE.EXP.GNFS.ZS':
            case 'NE.IMP.GNFS.ZS':
                // Persentase untuk Inflasi, Ekspor, dan Impor
                return number_format($value, 2) . '%';
                
            default:
                return $value;
        }
    }
}