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
        Schema::rename('request_export_logs', 'report_export_logs');
    }

    public function down(): void
    {
        Schema::rename('report_export_logs', 'request_export_logs');
    }
};
