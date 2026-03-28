<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {

            // add unit_id after product_id (optional positioning)
            $table->foreignId('unit_id')
                ->after('product_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();

            // optional index (recommended)
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {

            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
