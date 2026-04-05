<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            $table->text('edit_data')->nullable()->after('difference_in_stock');
        });
    }

    public function down(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            $table->dropColumn('edit_data');
        });
    }
};