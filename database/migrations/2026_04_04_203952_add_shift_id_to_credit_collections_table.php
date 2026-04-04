<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_collections', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_id')->nullable()->after('cash_break_id');

            // Optional: Foreign key (if shifts table exists)
            $table->foreign('shift_id')
                  ->references('id')
                  ->on('shifts')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('credit_collections', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });
    }
};