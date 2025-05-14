<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalsToRefundsTable extends Migration
{
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->integer('total_item_qty')->after('items_refund')->nullable();
            $table->decimal('total_item_price', 10, 2)->after('total_item_qty')->nullable();
            $table->decimal('total_mrp', 10, 2)->after('total_item_price')->nullable();
        });
    }

    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn(['total_item_qty', 'total_item_price', 'total_mrp']);
        });
    }
}
