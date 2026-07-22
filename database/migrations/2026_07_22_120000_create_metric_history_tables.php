<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_economic_histories', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->decimal('gdp', 18, 2)->nullable();
            $table->decimal('inflation_rate', 7, 3)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->cascadeOnDelete();
            $table->index(['country_code', 'recorded_at']);
        });

        Schema::create('port_weather_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained()->cascadeOnDelete();
            $table->decimal('temp', 5, 2)->nullable();
            $table->decimal('rain', 7, 2)->nullable();
            $table->decimal('wind_speed', 7, 2)->nullable();
            $table->string('storm_risk_status', 16)->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['port_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('port_weather_histories');
        Schema::dropIfExists('country_economic_histories');
    }
};
