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
        Schema::create('request_export_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exported_by')->constrained('users')->cascadeOnDelete();
            $table->enum('export_format', allowed: ['PDF', 'EXCEL']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_export_log');
    }
};
