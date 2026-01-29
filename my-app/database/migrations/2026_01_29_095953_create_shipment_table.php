<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->cascadeOnDelete();
            $table->integer('batch_number');
            $table->foreignId('shipped_by')->constrained('users')->cascadeOnDelete();
            $table->date('shipped_date')->nullable();
            $table->date('received_date')->nullable();
            $table->enum('status', [
                               'SHIPPED',
                               'RECEIVED'    
            ])->default('SHIPPED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment');
        
    }
};
