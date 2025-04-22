<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cash_breakdowns', function (Blueprint $table) {
            $table->string('type')->default('cash')->after('id'); // Default value set to 'cash'
        });
    }

    public function down()
    {
        Schema::table('cash_breakdowns', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

};
