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
        Schema::table('request_export_logs', function (Blueprint $table) {
            // 🔴 Drop foreign key first
            $table->dropForeign(['request_id']);

            // 🔴 Then drop column
            $table->dropColumn('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_export_logs', function (Blueprint $table) {
            $table->foreignId('request_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
        });
    }
};
