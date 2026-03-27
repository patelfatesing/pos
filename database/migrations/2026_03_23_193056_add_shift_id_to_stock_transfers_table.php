<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {

            // ✅ Column add only if not exists
            if (!Schema::hasColumn('stock_transfers', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')
                      ->nullable()
                      ->after('transfer_by');
            }
        });

        // ✅ Add foreign key separately (safe way)
        Schema::table('stock_transfers', function (Blueprint $table) {
            try {
                $table->foreign('shift_id')
                    ->references('id')
                    ->on('shift_closings')
                    ->nullOnDelete();
            } catch (\Exception $e) {
                // ignore if already exists or error
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {

            // ✅ Drop FK safely
            try {
                $table->dropForeign(['shift_id']);
            } catch (\Exception $e) {
                // ignore
            }

            // ✅ Drop column only if exists
            if (Schema::hasColumn('stock_transfers', 'shift_id')) {
                $table->dropColumn('shift_id');
            }
        });
    }
};