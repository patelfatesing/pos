<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReorderCategoryFieldsInProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->after('description')->change();
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->change();
        });
    }

    public function down()
    {
        // Optional: Add logic to revert to previous order if needed
    }
}
