<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->decimal('weather_score', 5, 2)->nullable();
            $table->decimal('inflation_score', 5, 2)->nullable();
            $table->decimal('exchange_score', 5, 2)->nullable();
            $table->decimal('news_score', 5, 2)->nullable();
            $table->decimal('exchange_rate', 18, 6)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->unsignedTinyInteger('data_coverage')->default(0);
            $table->string('risk_level', 16)->default('UNKNOWN');
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->cascadeOnDelete();
            $table->index(['country_code', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
    }
};
