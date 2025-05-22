<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpiAmountToCreditCollectionsTable extends Migration
{
    public function up()
    {
        Schema::table('credit_collections', function (Blueprint $table) {
            $table->decimal('upi_amount', 12, 2)->nullable()->after('amount');
        });
    }

    public function down()
    {
        Schema::table('credit_collections', function (Blueprint $table) {
            $table->dropColumn('upi_amount');
        });
    }
}
