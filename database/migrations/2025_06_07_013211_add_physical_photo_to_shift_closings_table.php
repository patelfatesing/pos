<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->string('physical_photo')->nullable()->after('physical_stock_added');
        });
    }

    public function down(): void
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn('physical_photo');
        });
    }
};

