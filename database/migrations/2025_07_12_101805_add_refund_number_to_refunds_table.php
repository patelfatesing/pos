<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('refund_number')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('refund_number');
        });
    }
};
