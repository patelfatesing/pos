<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUseCreditTypeInPartyUsersTable extends Migration
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
            // Revert back to enum if needed
            $table->enum('use_credit', ['Yes', 'No'])->default('Yes')->change();
        });
    }
}
