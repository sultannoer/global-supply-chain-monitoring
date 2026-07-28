<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The United States' real GDP exceeds DECIMAL(15,2)'s maximum value.
        // Blueprint::change() works on both MySQL and the SQLite test database.
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('gdp', 20, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('gdp', 15, 2)->nullable()->change();
        });
    }
};
