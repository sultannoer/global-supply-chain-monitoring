<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique(); // Nomor resi/kontainer unik
            
            // Relasi ke tabel pelabuhan asal dan pelabuhan tujuan
            $table->foreignId('origin_port_id')->constrained('ports')->onDelete('cascade');
            $table->foreignId('destination_port_id')->constrained('ports')->onDelete('cascade');
            
            // Data Logistik & Estimasi Waktu (ETA Monitoring)
            $table->dateTime('departure_date');
            $table->dateTime('estimated_arrival_date');
            $table->dateTime('actual_arrival_date')->nullable(); // Terisi jika kapal sudah bersandar
            
            // Perhitungan Finansial (Currency Change Monitoring)
            $table->double('initial_cost'); // Biaya awal saat barang dikirim
            $table->double('current_cost')->nullable(); // Nilai fluktuatif setelah terkena kurs real-time API
            
            // Fitur Utama UAS: Core Risk Engine Scoring (LOW, MEDIUM, HIGH)
            $table->enum('risk_status', ['LOW', 'MEDIUM', 'HIGH'])->default('LOW');
            $table->text('risk_reason')->nullable(); // Alasan status risiko (misal: "Badai di Pelabuhan Tujuan + Inflasi Naik")
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};