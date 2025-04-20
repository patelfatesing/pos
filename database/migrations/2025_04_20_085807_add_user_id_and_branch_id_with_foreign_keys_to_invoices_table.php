<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add user_id and branch_id columns
            $table->unsignedBigInteger('user_id')->nullable()->after('status');
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_id');

            // Add foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the foreign keys
            $table->dropForeign(['user_id']);
            $table->dropForeign(['branch_id']);

            // Drop the columns
            $table->dropColumn(['user_id', 'branch_id']);
        });
    }
};
