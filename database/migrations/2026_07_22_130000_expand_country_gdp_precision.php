<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The United States' real GDP exceeds DECIMAL(15,2)'s maximum value.
        DB::statement('ALTER TABLE countries MODIFY gdp DECIMAL(20,2) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE countries MODIFY gdp DECIMAL(15,2) NULL');
    }
};
