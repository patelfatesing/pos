<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('stock_requests', function (Blueprint $table) {
        // If `requested_by` already exists and you want to change its reference:
        // $table->dropForeign(['requested_by']);
        $table->foreign('requested_by')->references('id')->on('branches')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('stock_requests', function (Blueprint $table) {
        $table->dropForeign(['requested_by']);
        // Revert back to original if needed (e.g., users table)
        $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
    });
}

};
