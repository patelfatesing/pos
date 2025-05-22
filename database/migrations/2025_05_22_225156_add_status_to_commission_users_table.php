<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToCommissionUsersTable extends Migration
{
    public function up()
    {
        Schema::table('commission_users', function (Blueprint $table) {
            $table->string('status')->default('Active')->after('is_deleted');
        });
    }

    public function down()
    {
        Schema::table('commission_users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}

