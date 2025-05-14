<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartyAmountToRefundsTable extends Migration
{
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->decimal('party_amount', 10, 2)->after('total_mrp')->nullable();
        });
    }

    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('party_amount');
        });
    }
}
