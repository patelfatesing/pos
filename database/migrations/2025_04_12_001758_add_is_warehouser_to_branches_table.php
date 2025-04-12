<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('branches', function (Blueprint $table) {
        $table->enum('is_warehouser', ['yes', 'no'])->default('no')->after('name');
    });
}

public function down()
{
    Schema::table('branches', function (Blueprint $table) {
        $table->dropColumn('is_warehouser');
    });
}

};
