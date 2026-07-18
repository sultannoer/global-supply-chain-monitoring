<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); 
            $table->string('name');              
            $table->string('currency_code', 3);  
            
            
            $table->decimal('inflation_rate', 5, 2)->nullable(); 
            $table->bigInteger('gdp')->nullable();
            
            $table->timestamps();
        });
    }

  
     
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};