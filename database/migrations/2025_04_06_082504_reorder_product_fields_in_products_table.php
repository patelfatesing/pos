<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReorderProductFieldsInProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Move columns to follow `brand`
            $table->string('size')->after('brand')->change();
            $table->string('sku')->after('size')->change();
            $table->decimal('abv', 5, 2)->nullable()->after('sku')->change();
            $table->string('barcode')->nullable()->after('abv')->change();
        });
    }

    public function down()
    {
        // This is optional and depends on your original order.
        Schema::table('products', function (Blueprint $table) {
            // Revert to old order if needed (not required unless critical)
        });
    }
}
