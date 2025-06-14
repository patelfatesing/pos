<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShiftNoToShiftClosingsTable extends Migration
{
    public function up()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->string('shift_no', 250)->after('id')->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropUnique(['shift_no']);
            $table->dropColumn('shift_no');
        });
    }
}
