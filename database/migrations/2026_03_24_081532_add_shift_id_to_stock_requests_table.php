<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {

            // Step 1: nullable (safe for existing data)
            $table->unsignedBigInteger('shift_id')
                  ->nullable()
                  ->after('store_id');

            // Foreign Key
            $table->foreign('shift_id')
                  ->references('id')
                  ->on('shift_closings')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {

            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });
    }
};