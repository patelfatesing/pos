<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('products', function (Blueprint $table) {
        $table->timestamp('created_at')->useCurrent()->change();
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->timestamp('created_at')->nullable()->change(); // Or revert as needed
    });
}

};
