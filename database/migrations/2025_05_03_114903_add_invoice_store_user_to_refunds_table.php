<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn(['invoice_id', 'store_id', 'user_id']);
        });
    }
    
};
