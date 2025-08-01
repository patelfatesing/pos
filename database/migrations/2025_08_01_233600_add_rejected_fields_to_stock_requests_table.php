<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectedFieldsToStockRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('reject_reason')->nullable()->after('rejected_at');
        });
    }

    public function down()
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn(['rejected_at', 'reject_reason']);
        });
    }
}
