<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashDiscrepancyToShiftClosingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            // Add cash_discrepancy column
            $table->decimal('cash_discrepancy', 10, 2)->nullable()->after('closing_cash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            // Remove cash_discrepancy column
            $table->dropColumn('cash_discrepancy');
        });
    }
}
