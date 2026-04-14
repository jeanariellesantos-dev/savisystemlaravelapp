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
        //
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dealership_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->integer('quantity');

            $table->integer('starting_balance');
            $table->integer('ending_balance')->unsigned();

            $table->string('reference_type')->nullable(); // e.g. shipment, operation,
            $table->unsignedBigInteger('reference_id')->nullable(); //e.g. request id

            $table->string('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['product_id', 'dealership_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
