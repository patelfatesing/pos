<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductsTableStructure extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop old/unwanted columns
            $table->dropColumn([
                'product_type',
                'code',
                'barcode_symbology',
                'category',
                'cost',
                'price',
                'tax_method',
                'quantity',
            ]);

            // Add new fields
            $table->string('brand')->after('name');
            $table->foreignId('category_id')->constrained('categories')->after('brand');
            $table->foreignId('subcategory_id')->nullable()->constrained('sub_categories')->after('category_id');
            $table->string('size')->after('subcategory_id');
            $table->string('sku')->unique()->after('size');
            $table->decimal('abv', 5, 2)->nullable()->after('sku');
            $table->string('barcode')->nullable()->after('abv');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove newly added fields
            $table->dropForeign(['category_id']);
            $table->dropForeign(['subcategory_id']);

            $table->dropColumn([
                'brand',
                'category_id',
                'subcategory_id',
                'size',
                'sku',
                'abv',
                'barcode',
            ]);

            // Restore dropped fields
            $table->string('product_type')->nullable();
            $table->string('code')->nullable();
            $table->string('barcode_symbology')->nullable();
            $table->string('category')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('tax_method')->nullable();
            $table->integer('quantity')->nullable();
        });
    }
}
