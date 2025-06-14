<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1. Create separate non‑unique indexes for product_id and branch_id
     *    (needed by the FK constraints).
     * 2. Drop the old composite unique index.
     */
    public function up(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            // ① Make sure plain indexes exist so MySQL doesn't complain
            //    when we remove the composite unique key.
            $table->index('product_id');   // daily_product_stocks_product_id_index
            $table->index('branch_id');    // daily_product_stocks_branch_id_index
        });

        Schema::table('daily_product_stocks', function (Blueprint $table) {
            // ② Now we can safely drop the unique key
            //    Default name Laravel generated:
            $table->dropUnique('daily_product_stocks_product_id_branch_id_date_unique');
            // If you renamed the index, swap in the real name.
        });
    }

    /**
     * Rollback: put the unique key back and drop the helper indexes.
     */
    public function down(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            // Re‑add the composite unique constraint
            $table->unique(['product_id', 'branch_id', 'date']);

            // Remove the single‑column helper indexes
            $table->dropIndex(['product_id']);
            $table->dropIndex(['branch_id']);
        });
    }
};
