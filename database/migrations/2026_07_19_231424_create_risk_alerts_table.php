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
Schema::create('risk_alerts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('cascade');
        $table->foreignId('port_id')->nullable()->constrained()->onDelete('cascade');
        $table->string('alert_level'); // INFO, WARNING, CRITICAL
        $table->string('risk_type');   // WEATHER, GEOPOLITICS, ECONOMIC
        $table->text('message');
        $table->boolean('is_resolved')->default(false);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_alerts');
    }
};
