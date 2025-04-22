<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('cash_amount', 10, 2)->default(0)->after('id');
            $table->decimal('upi_amount', 10, 2)->default(0)->after('cash_amount');
        });
    }
    
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['cash_amount', 'upi_amount']);
        });
    }
};
