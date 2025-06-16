<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTotalPurchaseItemsDefaultInCreditHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->integer('total_purchase_items')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->integer('total_purchase_items')->change(); // remove default (may require DB-specific code)
        });
    }
}

