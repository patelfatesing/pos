<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStatusInCreditHistoriesTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE credit_histories MODIFY status ENUM('paid', 'unpaid', 'partial_paid') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE credit_histories MODIFY status ENUM('paid', 'unpaid') NOT NULL");
    }
}

