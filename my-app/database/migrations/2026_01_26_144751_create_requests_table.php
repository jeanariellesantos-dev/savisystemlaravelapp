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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->string('requestor_id')->constrained('users');
            $table->text('description');
            $table->enum('status', [
                    'PENDING_ACCOUNTING',
                    'PENDING_SUPERVISOR',
                    'PENDING_INVENTORY',
                    'FULFILLED',
                    'REJECTED'
            ])->default('PENDING_ACCOUNTING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
