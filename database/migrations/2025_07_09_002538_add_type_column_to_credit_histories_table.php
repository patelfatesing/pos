<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->enum('transaction_kind', ['order', 'refund'])
                  ->default('order')
                  ->after('type'); // or after another column you prefer
        });
    }

    public function down()
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->dropColumn('transaction_kind');
        });
    }
};
