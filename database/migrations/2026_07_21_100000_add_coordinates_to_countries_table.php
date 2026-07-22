<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('countries', 'latitude')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->after('language');
            });
        }

        if (! Schema::hasColumn('countries', 'longitude')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }
    }

    public function down(): void
    {
        $columns = array_filter(['latitude', 'longitude'], fn (string $column) => Schema::hasColumn('countries', $column));

        if ($columns !== []) {
            Schema::table('countries', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
