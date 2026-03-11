<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE request_status_logs
            MODIFY status VARCHAR(50)
            DEFAULT 'PENDING_ACCOUNTING'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE request_status_logs
            MODIFY status ENUM(
                'PENDING_ACCOUNTING',
                'PENDING_CLUSTER_HEAD',
                'PENDING_SUPERVISOR',
                'PENDING_INVENTORY',
                'SHIPPED',
                'RECEIVED',
                'COMPLETED',
                'REJECTED',
                'ON_HOLD',
                'CANCELLED'
            )
            DEFAULT 'PENDING_ACCOUNTING'
        ");
    }
};
