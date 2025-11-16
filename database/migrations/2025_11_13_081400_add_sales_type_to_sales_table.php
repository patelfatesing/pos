<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesTypeToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the enum column 'sales_type' with default 'normal'
        Schema::table('sales', function (Blueprint $table) {
            // place column where you prefer; here we add after 'invoice_id'
            $table->enum('sales_type', ['normal', 'one_time'])
                  ->default('normal')
                  ->after('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('sales_type');
        });
    }
}
