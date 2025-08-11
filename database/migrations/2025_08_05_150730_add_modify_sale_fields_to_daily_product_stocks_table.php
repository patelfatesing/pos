<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModifySaleFieldsToDailyProductStocksTable extends Migration
{
    public function up(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            $table->integer('modify_sale_add_qty')->default(0)->after('sold_stock');
            $table->integer('modify_sale_remove_qty')->default(0)->after('modify_sale_add_qty');
        });
    }

    public function down(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            $table->dropColumn(['modify_sale_add_qty', 'modify_sale_remove_qty']);
        });
    }
}
