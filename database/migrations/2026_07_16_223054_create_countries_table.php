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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // Contoh: IDN, USA, CHN
            $table->string('name');              // Nama Negara
            $table->string('currency_code', 3);  // Kode Mata Uang (IDR, USD)
            
            // Kolom nullable untuk menampung data dari World Bank API nanti
            $table->decimal('inflation_rate', 5, 2)->nullable(); 
            $table->bigInteger('gdp')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};