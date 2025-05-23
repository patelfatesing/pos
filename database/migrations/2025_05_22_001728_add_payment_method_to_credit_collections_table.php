<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodToCreditCollectionsTable extends Migration
{
    public function up()
    {
        Schema::table('credit_collections', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('note_data');
            // nullable in case old records don't have this data yet
        });
    }

    public function down()
    {
        Schema::table('credit_collections', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
}
