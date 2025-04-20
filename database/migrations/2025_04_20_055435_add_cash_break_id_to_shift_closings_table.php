<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->unsignedBigInteger('cash_break_id')->nullable()->after('id'); // You can change 'id' to position it where you like
        });
    }

    public function down()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn('cash_break_id');
        });
    }

};
