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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_type')->nullable();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->string('barcode_symbology');
            $table->string('category');
            $table->decimal('cost', 10, 2);
            $table->decimal('price', 10, 2);
            $table->string('tax_method');
            $table->integer('quantity');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
