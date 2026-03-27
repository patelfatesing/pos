<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // shift_closings table
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->enum('admin_status', ['verify', 'unverify'])
                  ->default('unverify')
                  ->after('status');
        });

        // stock_requests table
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->enum('admin_status', ['verify', 'unverify'])
                  ->default('unverify')
                  ->after('status');
        });

        // stock_transfers table
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->enum('admin_status', ['verify', 'unverify'])
                  ->default('unverify')
                  ->after('status');
        });
    }

    public function down(): void
    {
        // shift_closings
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn('admin_status');
        });

        // stock_requests
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn('admin_status');
        });

        // stock_transfers
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropColumn('admin_status');
        });
    }
};