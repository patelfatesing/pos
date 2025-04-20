<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->integer('total_product')->default(0)->after('id'); // or place after any relevant column
            $table->integer('total_quantity')->default(0)->after('total_product');
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn(['total_product', 'total_quantity']);
        });
    }
};

