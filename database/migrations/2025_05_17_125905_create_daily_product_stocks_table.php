<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyProductStocksTable extends Migration
{
    public function up()
    {
        Schema::create('daily_product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('opening_stock')->default(0);
            $table->integer('added_stock')->default(0);
            $table->integer('transferred_stock')->default(0); // total outgoing
            $table->integer('sold_stock')->default(0);
            $table->integer('closing_stock')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'branch_id', 'date']); // prevent duplicate records for same product, branch, date
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_product_stocks');
    }
}

