<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('demand_orders', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('demand_orders', function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
};
