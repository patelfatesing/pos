<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_requests', 'store_id')) {
                $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            } else {
                $table->unsignedBigInteger('store_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            // Rollback logic (optional)
            // $table->dropForeign(['store_id']);
            // $table->dropColumn('store_id');
        });
    }
};
