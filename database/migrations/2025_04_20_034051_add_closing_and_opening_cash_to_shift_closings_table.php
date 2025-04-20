<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->decimal('closing_cash', 10, 2)->nullable()->after('opening_cash');
        });
    }

    public function down()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn(['opening_cash', 'closing_cash']);
        });
    }
};
