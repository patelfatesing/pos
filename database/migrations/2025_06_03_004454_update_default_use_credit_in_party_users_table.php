<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDefaultUseCreditInPartyUsersTable extends Migration
{
    public function up()
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->decimal('use_credit', 10, 2)->default(0.00)->change();
        });
    }

    public function down()
    {
        Schema::table('party_users', function (Blueprint $table) {
            // Optionally remove default if needed
            $table->decimal('use_credit', 10, 2)->default(null)->change();
        });
    }
}
