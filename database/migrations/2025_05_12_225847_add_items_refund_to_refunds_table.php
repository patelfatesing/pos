<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->text('items_refund')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('items_refund');
        });
    }
};
