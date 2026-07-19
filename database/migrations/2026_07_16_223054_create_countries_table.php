<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->string('code', 3)->primary(); // ISO Code (IDN, NLD, USA)
            $table->string('name');
            $table->string('region');
            $table->string('currency_code', 3);
            $table->string('language');
            
            // DATA MAKROEKONOMI (World Bank)
            $table->decimal('gdp', 15, 2)->nullable();
            $table->decimal('inflation_rate', 5, 2)->nullable();
            $table->bigInteger('population')->nullable();
            $table->decimal('export_volume', 15, 2)->nullable();
            $table->decimal('import_volume', 15, 2)->nullable();
            
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