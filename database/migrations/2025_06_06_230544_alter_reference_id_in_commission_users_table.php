<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReferenceIdInCommissionUsersTable extends Migration
{
public function up()
{
    Schema::table('commission_users', function (Blueprint $table) {
        $table->string('reference_id', 50)->nullable()->change();
    });
}

public function down()
{
    Schema::table('commission_users', function (Blueprint $table) {
        $table->bigInteger('reference_id')->nullable()->change();
    });
}

}
