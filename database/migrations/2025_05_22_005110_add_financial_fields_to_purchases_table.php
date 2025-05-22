<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialFieldsToPurchasesTable extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->float('vat')->nullable();
            $table->float('surcharge_on_vat')->nullable();
            $table->float('blf')->nullable();
            $table->float('permit_fee')->nullable();
            $table->float('rsgsm_purchase')->nullable();
            $table->float('case_purchase')->nullable();
            $table->float('case_purchase_per')->nullable();
            $table->float('case_purchase_amt')->nullable();
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'vat',
                'surcharge_on_vat',
                'blf',
                'permit_fee',
                'rsgsm_purchase',
                'case_purchase',
                'case_purchase_per',
                'case_purchase_amt',
            ]);
        });
    }
}

