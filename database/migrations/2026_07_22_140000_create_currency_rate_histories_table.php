<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_rate_histories', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3);
            $table->decimal('rate_to_usd', 20, 8)->nullable();
            $table->string('source', 80);
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['currency_code', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_rate_histories');
    }
};
