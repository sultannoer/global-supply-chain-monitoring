<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positive_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->timestamps();
        });

        Schema::create('negative_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->timestamps();
        });

        Schema::create('news_sentiments', function (Blueprint $table) {
            $table->id();
            $table->string('article_hash', 64)->unique();
            $table->string('query')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('positive_score')->default(0);
            $table->unsignedSmallInteger('negative_score')->default(0);
            $table->string('sentiment', 12)->default('Neutral');
            $table->timestamp('analyzed_at')->useCurrent();
            $table->timestamps();

            $table->index(['sentiment', 'analyzed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sentiments');
        Schema::dropIfExists('negative_words');
        Schema::dropIfExists('positive_words');
    }
};
