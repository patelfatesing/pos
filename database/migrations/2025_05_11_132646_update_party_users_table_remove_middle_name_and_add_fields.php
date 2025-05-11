<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('party_users', function (Blueprint $table) {

             if (Schema::hasColumn('party_users', 'middle_name')) {
            DB::statement('ALTER TABLE party_users DROP COLUMN middle_name');
        }
            // Remove the 'middle_name' column
            // $table->dropColumn('middle_name');

            // Add new fields
            $table->decimal('left_credit', 10, 2)->nullable()->after('credit_points');
            $table->enum('payment_status', ['full_paid', 'partial_paid', 'pending'])->default('pending')->after('left_credit');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('payment_status');
            $table->enum('is_delete', ['Yes', 'No'])->default('No')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('party_users', function (Blueprint $table) {
            // Restore 'middle_name'
            $table->string('middle_name')->nullable();

            // Drop added fields
            $table->dropColumn(['left_credit', 'payment_status', 'status', 'is_delete']);
        });
    }
};

