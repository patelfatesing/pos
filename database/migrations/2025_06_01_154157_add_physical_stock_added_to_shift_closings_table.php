<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->boolean('physical_stock_added')->default(false)->after('closing_sales');
        });
    }

    public function down(): void
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn('physical_stock_added');
        });
    }
};
