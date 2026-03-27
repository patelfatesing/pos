<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {

            // Admin Status
            $table->enum('admin_status', ['verify', 'unverify'])
                ->default('unverify')
                ->after('grand_total');

            // Shift ID
            $table->unsignedBigInteger('shift_id')
                ->nullable()
                ->after('admin_status');

            // Optional Foreign Key (if shift_closings table exists)
            $table->foreign('shift_id')
                ->references('id')
                ->on('shift_closings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {

            // Drop foreign key first
            $table->dropForeign(['shift_id']);

            // Drop columns
            $table->dropColumn(['admin_status', 'shift_id']);
        });
    }
};
