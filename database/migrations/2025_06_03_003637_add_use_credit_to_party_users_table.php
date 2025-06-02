<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseCreditToPartyUsersTable extends Migration
{
    public function up()
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->enum('use_credit', ['Yes', 'No'])->default('Yes')->after('is_delete');
        });
    }

    public function down()
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->dropColumn('use_credit');
        });
    }
}
