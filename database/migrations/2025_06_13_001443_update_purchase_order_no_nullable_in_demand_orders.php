<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePurchaseOrderNoNullableInDemandOrders extends Migration
{
    public function up()
    {
        Schema::table('demand_orders', function (Blueprint $table) {
            // First drop the unique index
            $table->dropUnique('demand_orders_purchase_order_no_unique');
        });

        Schema::table('demand_orders', function (Blueprint $table) {
            // Now make the column nullable
            $table->string('purchase_order_no')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('demand_orders', function (Blueprint $table) {
            $table->string('purchase_order_no')->nullable(false)->change();
            $table->unique('purchase_order_no');
        });
    }
}
