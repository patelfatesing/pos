<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_extra_fields_to_inventories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->date('mfg_date')->nullable()->after('expiry_date');
            $table->decimal('discount_price', 10, 2)->nullable()->after('sell_price');
            $table->decimal('discount_amt', 10, 2)->nullable()->after('discount_price');
            $table->unsignedInteger('case_size')->nullable()->after('discount_amt');
            $table->unsignedInteger('box_unit')->nullable()->after('case_size');
            $table->string('secondary_unitx')->nullable()->after('box_unit');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn([
                'mfg_date',
                'discount_price',
                'discount_amt',
                'case_size',
                'box_unit',
                'secondary_unitx',
            ]);
        });
    }
};
