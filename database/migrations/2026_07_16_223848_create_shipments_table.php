<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique(); 
            
           
            $table->foreignId('origin_port_id')->constrained('ports')->onDelete('cascade');
            $table->foreignId('destination_port_id')->constrained('ports')->onDelete('cascade');
            
            
            $table->dateTime('departure_date');
            $table->dateTime('estimated_arrival_date');
            $table->dateTime('actual_arrival_date')->nullable(); 
            
            
            $table->double('initial_cost'); 
            $table->double('current_cost')->nullable();
            
            
            $table->enum('risk_status', ['LOW', 'MEDIUM', 'HIGH'])->default('LOW');
            $table->text('risk_reason')->nullable(); 
            
            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};