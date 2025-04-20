<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('reorder_level')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->decimal('discount_amt', 10, 2)->nullable();
            $table->string('case_size')->nullable();
            $table->string('box_unit')->nullable();
            $table->string('secondary_unitx')->nullable();
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn([
                'reorder_level',
                'cost_price',
                'sell_price',
                'discount_price',
                'discount_amt',
                'case_size',
                'box_unit',
                'secondary_unitx',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->integer('reorder_level')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->decimal('discount_amt', 10, 2)->nullable();
            $table->string('case_size')->nullable();
            $table->string('box_unit')->nullable();
            $table->string('secondary_unitx')->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'reorder_level',
                'cost_price',
                'sell_price',
                'discount_price',
                'discount_amt',
                'case_size',
                'box_unit',
                'secondary_unitx',
            ]);
        });
    }
};

