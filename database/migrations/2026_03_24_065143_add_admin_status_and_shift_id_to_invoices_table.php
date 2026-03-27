<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            // Admin Status
            $table->enum('admin_status', ['verify', 'unverify'])
                  ->default('unverify')
                  ->after('status');

            // Shift ID (Step 1: nullable for safety)
            $table->unsignedBigInteger('shift_id')
                  ->nullable()
                  ->after('branch_id');

            // Foreign Key
            $table->foreign('shift_id')
                  ->references('id')
                  ->on('shift_closings')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {

            // Drop FK first
            $table->dropForeign(['shift_id']);

            // Drop columns
            $table->dropColumn(['admin_status', 'shift_id']);
        });
    }
};