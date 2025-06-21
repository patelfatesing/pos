<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashAddedToShiftClosingsTable extends Migration
{
    public function up()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->decimal('cash_added', 10, 2)->nullable()->after('opening_cash');
        });
    }

    public function down()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn('cash_added');
        });
    }
}
