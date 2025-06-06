<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeLastNameNullableInPartyUsersTable extends Migration
{
    public function up()
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->string('last_name')->nullable(false)->change();
        });
    }
}