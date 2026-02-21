<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
                'REJECTED'
            ) 
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
                'PENDING_SUPERVISOR',
                'PENDING_INVENTORY',
                'SHIPPED',
                'RECEIVED',
                'COMPLETED',
                'REJECTED'
            ) 
            DEFAULT 'PENDING_ACCOUNTING'
        ");
    }
};