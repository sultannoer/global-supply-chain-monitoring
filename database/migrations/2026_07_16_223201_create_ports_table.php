<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country_code', 3); 
            
            
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            
            $table->decimal('temp', 4, 1)->nullable(); 
            $table->decimal('rain', 5, 2)->nullable(); 
            $table->decimal('wind_speed', 5, 2)->nullable(); 
            $table->string('storm_risk_status')->default('Low'); 
            
            
            $table->integer('risk_score')->default(0); 
            
            $table->timestamps();

            
            $table->foreign('country_code')
                  ->references('code')
                  ->on('countries')
                  ->onDelete('cascade');
        });
    }

   
   
    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};