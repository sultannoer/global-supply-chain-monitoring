<?php

namespace App\Services;

use App\Models\Port;
use App\Models\Shipment;
use App\Models\RiskAlert;

class RiskAssessmentService
{
    /**
     * Menghitung skor risiko akumulatif untuk Pelabuhan (Skala 0 - 100)
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

        $finalScore = min(max($score, 0), 100);
        $port->update(['risk_score' => $finalScore]);

        // 🚨 TRIGGER OTOMATIS: Catat log jika pelabuhan masuk zona bahaya
        if ($finalScore >= 50) {
            RiskAlert::updateOrCreate(
                ['port_id' => $port->id, 'is_resolved' => false, 'risk_type' => 'WEATHER'],
                [
                    'alert_level' => $finalScore >= 75 ? 'CRITICAL' : 'WARNING',
                    'message' => "⚠️ PELABUHAN RAWAN: Terminal [{$port->name}] terdeteksi memiliki Skor Risiko tinggi ({$finalScore}/100) akibat cuaca buruk. Operasional crane kontainer berpotensi tertunda."
                ]
            );
        } else {
            // Jika cuaca membaik, tandai alert sebelumnya sebagai resolved
            RiskAlert::where('port_id', $port->id)->update(['is_resolved' => true]);
        }

        return $finalScore;
    }

    /**
     * Menghitung skor risiko akumulatif untuk Perjalanan Kapal / Shipment (Skala 0 - 100)
     */
    public function calculateShipmentRisk(Shipment $shipment): int
    {
        $score = 0;
        $destinationPort = $shipment->destinationPort;

        // 1. FAKTOR KONDISI CUACA DI PELABUHAN TUJUAN (Bobot: 60%)
        if ($destinationPort) {
            $portRisk = $destinationPort->risk_score;
            $score += ($portRisk * 0.6);
        }

        // 2. FAKTOR KERAWANAN EKONOMI / INFLASI NEGARA TUJUAN (Bobot: 40%)
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
            $score += 15; 
        }

        $finalScore = min(max((int) $score, 0), 100);
        $shipment->update(['risk_score' => $finalScore]);

        // 🚨 TRIGGER OTOMATIS: Catat log jika kapal kargo menghadapi risiko tinggi
        if ($finalScore >= 45) {
            RiskAlert::updateOrCreate(
                ['shipment_id' => $shipment->id, 'is_resolved' => false],
                [
                    'alert_level' => $finalScore >= 70 ? 'CRITICAL' : 'WARNING',
                    'risk_type' => $inflation > 12 ? 'ECONOMIC' : 'WEATHER',
                    'message' => "🚢 ANCAMAN RUTE: Kapal [{$shipment->vessel_name}] dengan manifes #{$shipment->tracking_number} menghadapi akumulasi risiko kargo ({$finalScore}/100) menuju pelabuhan target."
                ]
            );
        } else {
            RiskAlert::where('shipment_id', $shipment->id)->update(['is_resolved' => true]);
        }

        return $finalScore;
    }
}