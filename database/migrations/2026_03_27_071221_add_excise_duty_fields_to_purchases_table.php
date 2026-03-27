<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('excise_duty_80', 15, 2)->nullable()->after('excise_total_amount');
            $table->decimal('excise_duty_20', 15, 2)->nullable()->after('excise_duty_80');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['excise_duty_80', 'excise_duty_20']);
        });
    }
};
