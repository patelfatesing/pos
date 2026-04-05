<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('super_admin_status', ['verify','unverify'])
                  ->default('unverify')
                  ->after('admin_status');
        });

        // Stock Transfers
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->enum('super_admin_status', ['verify','unverify'])
                  ->default('unverify')
                  ->after('admin_status');
        });

        // Shift Closings
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->enum('super_admin_status', ['verify','unverify'])
                  ->default('unverify')
                  ->after('admin_status');
        });
    }

    public function down(): void
    {
        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('super_admin_status');
        });

        // Stock Transfers
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropColumn('super_admin_status');
        });

        // Shift Closings
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn('super_admin_status');
        });
    }
};
