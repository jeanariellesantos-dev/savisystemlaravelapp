<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE requests
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
            ALTER TABLE requests
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
