<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterShiftIdNullableInDailyProductStocksTable extends Migration
{
    public function up(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            // BIGINT(20) → unsignedBigInteger in Laravel
            // change() requires doctrine/dbal
            $table->unsignedBigInteger('shift_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            // Revert to NOT NULL (adjust default if you had one)
            $table->unsignedBigInteger('shift_id')->nullable(false)->change();
        });
    }
}
