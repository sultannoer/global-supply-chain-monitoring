<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique(); 
            $table->string('vessel_name'); 
            
            $table->unsignedBigInteger('origin_port_id');
            $table->unsignedBigInteger('destination_port_id');
            
            // Koordinat Live untuk Leaflet.js
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_lng', 11, 8)->nullable();
            
            // Tanggal & Prediksi (ETA)
            $table->date('departure_date')->nullable();
            $table->date('baseline_eta')->nullable(); 
            $table->date('adaptive_eta')->nullable(); 
            
            // Metrik Finansial (Sesuai Studi Kasus Devisa/Kurs)
            $table->decimal('initial_cost_usd', 15, 2)->default(0); 
            $table->decimal('current_exchange_rate', 10, 4)->nullable(); 
            $table->decimal('cargo_weight', 10, 2)->default(0); // <-- Ini yang bikin error tadi
            
            // Status Radar & Risiko
            $table->string('status')->default('DEPARTING'); 
            $table->integer('step')->default(0); // <-- Ini wajib untuk pergerakan kapal
            $table->integer('risk_score')->default(0); 
            
            $table->timestamps();

            $table->foreign('origin_port_id')->references('id')->on('ports')->onDelete('cascade');
            $table->foreign('destination_port_id')->references('id')->on('ports')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};