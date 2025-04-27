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
        Schema::create('demand_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendor_lists')->onDelete('cascade');
            $table->date('purchase_date');
            $table->string('purchase_order_no')->unique();
            $table->date('shipping_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['order', 'shipping', 'delivered'])->default('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demand_orders');
    }
};
