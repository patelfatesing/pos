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
            $table->unsignedBigInteger('role_id')->nullable()->after('password');
            $table->unsignedBigInteger('work_branch_id')->nullable()->after('role_id');
            $table->unsignedBigInteger('created_by')->nullable()->after('work_branch_id');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

            // Other Fields
            $table->enum('is_active', ['yes', 'no'])->default('yes')->after('updated_by'); // ENUM column
            $table->enum('is_deleted', ['yes', 'no'])->default('no')->after('is_active'); // ENUM column

            // Adding Foreign Key Constraints
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            $table->foreign('work_branch_id')->references('id')->on('branch')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
                // Drop Foreign Keys First
                $table->dropForeign(['role_id']);
                $table->dropForeign(['branch_id']);
                $table->dropForeign(['created_by']);
                $table->dropForeign(['updated_by']);
        
                // Drop Columns
                $table->dropColumn(['role_id', 'branch_id', 'is_active', 'is_deleted', 'created_by', 'updated_by']);
        
        });
    }
};
