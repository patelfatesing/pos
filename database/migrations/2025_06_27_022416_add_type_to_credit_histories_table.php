<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToCreditHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->enum('type', ['credit', 'debit'])->default('credit')->after('debit_amount');
        });
    }

    public function down()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
