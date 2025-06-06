<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotoToPartyUsersTable extends Migration
{
    public function up()
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('email'); // You can change position as needed
        });
    }

    public function down()
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
}
