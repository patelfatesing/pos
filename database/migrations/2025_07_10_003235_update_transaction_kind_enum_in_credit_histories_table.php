<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class UpdateTransactionKindEnumInCreditHistoriesTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE credit_histories 
            MODIFY COLUMN transaction_kind 
            ENUM('order', 'refund', 'collact_credit') 
            COLLATE utf8mb4_unicode_ci 
            NOT NULL DEFAULT 'order'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE credit_histories 
            MODIFY COLUMN transaction_kind 
            ENUM('order', 'refund') 
            COLLATE utf8mb4_unicode_ci 
            NOT NULL DEFAULT 'order'");
    }
}
