<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDebitAmountToCreditHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->decimal('debit_amount', 15, 2)->default(0.00)->after('credit_amount');
        });
    }

    public function down()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->dropColumn('debit_amount');
        });
    }
}
