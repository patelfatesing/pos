<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('demand_order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demand_order_id')->constrained('demand_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('barcode')->nullable();
            $table->decimal('mrp', 15, 2);
            $table->decimal('rate', 15, 2);
            $table->decimal('sell_price', 15, 2);
            $table->enum('delivery_status', ['partially', 'full'])->default('partially');
            $table->integer('delivery_quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demand_order_products');
    }
};
