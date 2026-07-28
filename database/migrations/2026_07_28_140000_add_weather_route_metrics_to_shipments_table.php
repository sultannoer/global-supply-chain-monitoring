<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedSmallInteger('weather_delay_hours')->default(0)->after('adaptive_eta');
            $table->string('route_weather_status')->default('Clear')->after('weather_delay_hours');
            $table->string('route_storm_name')->nullable()->after('route_weather_status');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['weather_delay_hours', 'route_weather_status', 'route_storm_name']);
        });
    }
};
