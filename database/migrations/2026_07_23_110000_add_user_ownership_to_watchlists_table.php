<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('watchlists', 'user_id')) {
            Schema::table('watchlists', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        // The original country_code index also backs the country foreign key,
        // so remove the foreign key before replacing the global unique index.
        Schema::table('watchlists', function (Blueprint $table) {
            $table->dropForeign(['country_code']);
            $table->dropUnique(['country_code']);
        });

        Schema::table('watchlists', function (Blueprint $table) {
            $table->unique(['user_id', 'country_code']);
            $table->foreign('country_code')->references('code')->on('countries')->cascadeOnDelete();
        });

        // Keep old global entries visible to the first admin when upgrading an
        // existing installation. New entries are always tied to their owner.
        $adminId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');
        if ($adminId) {
            DB::table('watchlists')->whereNull('user_id')->update(['user_id' => $adminId]);
        }
    }

    public function down(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'country_code']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->unique('country_code');
        });
    }
};
