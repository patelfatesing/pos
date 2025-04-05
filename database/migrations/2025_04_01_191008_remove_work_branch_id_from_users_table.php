<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the foreign key first
            $table->dropForeign(['work_branch_id']);

            // Now drop the column
            $table->dropColumn('work_branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('work_branch_id')->nullable()->after('role_id');
                $table->foreign('work_branch_id')->references('id')->on('branches')->onDelete('set null'); // Recreate the foreign key
            });
        });
    }
};
