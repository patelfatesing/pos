<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Excise breakup fields
            $table->decimal('permit_fee_excise', 15, 2)
                ->default(0.00)
                ->after('rsgsm_purchase');

            $table->decimal('vend_fee_excise', 15, 2)
                ->default(0.00)
                ->after('permit_fee_excise');

            $table->decimal('composite_fee_excise', 15, 2)
                ->default(0.00)
                ->after('vend_fee_excise');

            $table->decimal('excise_total_amount', 15, 2)
                ->default(0.00)
                ->after('composite_fee_excise');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'permit_fee_excise',
                'vend_fee_excise',
                'composite_fee_excise',
                'excise_total_amount',
            ]);
        });
    }
};
