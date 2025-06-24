<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeInvoiceIdNullableInCreditHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['invoice_id']);

            // Modify column to be nullable
            $table->unsignedBigInteger('invoice_id')->nullable()->change();

            // Re-add foreign key constraint
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->unsignedBigInteger('invoice_id')->nullable(false)->change();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }
}
