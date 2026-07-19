<?php

namespace App\Services;

use App\Models\Port;
use App\Models\Shipment;

class RiskAssessmentService
{
    /**
     * Menghitung skor risiko akumulatif untuk Pelabuhan (Skala 0 - 100)
     * Berdasarkan parameter cuaca live dari Open-Meteo.
     */
    public function calculatePortRisk(Port $port): int
    {
        $score = 0;

        // 1. FAKTOR STATUS BADAI (Bobot Maksimal: 40 Poin)
        if ($port->storm_risk_status === 'High') {
            $score += 40;
        } elseif ($port->storm_risk_status === 'Medium') {
            $score += 20;
        } else {
            $score += 5;
        }

        // 2. FAKTOR KECEPATAN ANGIN (Bobot Maksimal: 30 Poin)
        // Angin di atas 40 km/jam mulai mengganggu aktivitas bongkar muat crane kontainer
        $wind = (float) $port->wind_speed;
        if ($wind > 50) {
            $score += 30;
        } elseif ($wind > 30) {
            $score += 15;
        } else {
            $score += 5;
        }

        // 3. FAKTOR CURAH HUJAN (Bobot Maksimal: 30 Poin)
        $rain = (float) $port->rain;
        if ($rain > 15) {
            $score += 30;
        } elseif ($rain > 5) {
            $score += 15;
        } else {
            $score += 5;
        }

        // Pastikan skor tidak meluncur melebihi batas 100 atau kurang dari 0
        $finalScore = min(max($score, 0), 100);

        // Simpan langsung hasil kalkulasi ke database lokal pelabuhan ini
        $port->update(['risk_score' => $finalScore]);

        return $finalScore;
    }

    /**
     * Menghitung skor risiko akumulatif untuk Perjalanan Kapal / Shipment (Skala 0 - 100)
     * Menggabungkan risiko cuaca pelabuhan tujuan dan stabilitas inflasi ekonomi negara tujuan.
     */
    public function calculateShipmentRisk(Shipment $shipment): int
    {
        $score = 0;
        $destinationPort = $shipment->destinationPort;

        // 1. FAKTOR KONDISI CUACA DI PELABUHAN TUJUAN (Bobot: 60%)
        if ($destinationPort) {
            // Ambil skor risiko pelabuhan tujuan yang sudah dihitung sebelumnya
            $portRisk = $destinationPort->risk_score;
            $score += ($portRisk * 0.6);
        }

        // 2. FAKTOR KERAWANAN EKONOMI / INFLASI NEGARA TUJUAN (Bobot: 40%)
        // Inflasi tinggi (> 10%) meningkatkan risiko fluktuasi nilai mata uang saat kapal bersandar
        if ($destinationPort && $destinationPort->country) {
            $inflation = (float) $destinationPort->country->inflation_rate;
            
            if ($inflation > 12) {
                $score += 40;
            } elseif ($inflation > 6) {
                $score += 20;
            } else {
                $score += 10;
            }
        } else {
            $score += 15; // Default score jika data World Bank belum masuk
        }

        $finalScore = min(max((int) $score, 0), 100);

        // Update skor risiko dinamis kapal ke database lokal
        $shipment->update(['risk_score' => $finalScore]);

        return $finalScore;
    }
}