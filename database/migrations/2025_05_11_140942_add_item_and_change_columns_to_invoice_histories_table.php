<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemAndChangeColumnsToInvoiceHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('invoice_histories', function (Blueprint $table) {
            $table->integer('total_item_qty')->nullable()->after('items');
            $table->decimal('total_item_total', 10, 2)->nullable()->after('total_item_qty');
            $table->decimal('change_amount', 10, 2)->nullable()->after('total');
        });
    }

    public function down()
    {
        Schema::table('invoice_histories', function (Blueprint $table) {
            $table->dropColumn(['total_item_qty', 'total_item_total', 'change_amount']);
        });
    }
}
