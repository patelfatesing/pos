<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            $table->integer('physical_stock')->default(0)->after('closing_stock');
            $table->integer('difference_in_stock')->default(0)->after('physical_stock');
        });
    }

    public function down()
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            $table->dropColumn(['physical_stock', 'difference_in_stock']);
        });
    }


};
