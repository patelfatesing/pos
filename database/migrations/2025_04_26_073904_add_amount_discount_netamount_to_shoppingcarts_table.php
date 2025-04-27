<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0)->after('quantity');
            $table->decimal('discount', 10, 2)->default(0)->after('amount');
            $table->decimal('net_amount', 10, 2)->default(0)->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->dropColumn(['amount', 'discount', 'net_amount']);
        });
    }
};
