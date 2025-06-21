<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateInvoicesAddRemainingCreditAndChangeStatusDefault extends Migration
{
     public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('remaining_credit_pay', 10, 2)->default(0.00)->after('creditpay');
            $table->string('invoice_status')->default('unpaid')->after('status');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('remaining_credit_pay');
            $table->dropColumn('invoice_status');
        });
    }
}
