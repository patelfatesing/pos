<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashAndOnlineAmountToCreditCollectionsTable extends Migration
{
    public function up()
    {
        Schema::table('credit_collections', function (Blueprint $table) {
            $table->decimal('cash_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('online_amount', 12, 2)->nullable()->after('cash_amount');
        });
    }

    public function down()
    {
        Schema::table('credit_collections', function (Blueprint $table) {
            $table->dropColumn('cash_amount');
            $table->dropColumn('online_amount');
        });
    }
}
