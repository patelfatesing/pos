<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->decimal('mrp', 10, 2)->default(0)->after('net_amount');
        });
    }

    public function down(): void
    {
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->dropColumn('mrp');
        });
    }
};
