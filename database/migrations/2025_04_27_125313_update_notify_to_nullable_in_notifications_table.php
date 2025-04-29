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
        Schema::table('notifications', function (Blueprint $table) {
            // Drop existing foreign key first to avoid duplicate key error
            try {
                $table->dropForeign(['notify_to']);
            } catch (\Throwable $e) {
                // Ignore if it doesn't exist
            }

            // Make column nullable
            $table->unsignedBigInteger('notify_to')->nullable()->change();

            // Add foreign key with 'on delete set null'
            $table->foreign('notify_to')
                  ->references('id')
                  ->on('branches')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop foreign key first to avoid duplicate
            try {
                $table->dropForeign(['notify_to']);
            } catch (\Throwable $e) {
                // Ignore if not present
            }

            // Make column not nullable again
            $table->unsignedBigInteger('notify_to')->nullable(false)->change();

            // Reapply foreign key with original behavior (e.g., cascade)
            $table->foreign('notify_to')
                  ->references('id')
                  ->on('branches')
                  ->onDelete('cascade');
        });
    }
};
