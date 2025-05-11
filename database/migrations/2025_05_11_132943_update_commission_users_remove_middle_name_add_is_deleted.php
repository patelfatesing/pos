<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
         if (Schema::hasColumn('commission_users', 'middle_name')) {
            DB::statement('ALTER TABLE commission_users DROP COLUMN middle_name');
        }
        
        Schema::table('commission_users', function (Blueprint $table) {
            // Remove middle_name
            // $table->dropColumn('middle_name');

            // Add is_deleted field
            $table->enum('is_deleted', ['Yes', 'No'])->default('No')->after('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('commission_users', function (Blueprint $table) {
            // Restore middle_name
            $table->string('middle_name')->nullable();

            // Drop is_deleted field
            $table->dropColumn('is_deleted');
        });
    }
};
