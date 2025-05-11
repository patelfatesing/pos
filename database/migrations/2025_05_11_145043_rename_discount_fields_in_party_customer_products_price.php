<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('party_customer_products_price', function (Blueprint $table) {
            $table->renameColumn('discount_price', 'cust_discount_price');
            $table->renameColumn('discount_amt', 'cust_discount_amt');
        });
    }

    public function down(): void
    {
        Schema::table('party_customer_products_price', function (Blueprint $table) {
            $table->renameColumn('cust_discount_price', 'discount_price');
            $table->renameColumn('cust_discount_amt', 'discount_amt');
        });
    }
};
