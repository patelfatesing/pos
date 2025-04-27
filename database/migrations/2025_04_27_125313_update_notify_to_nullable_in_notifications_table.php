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
              // Make 'notify_to' nullable and update foreign key relationship
              $table->unsignedBigInteger('notify_to')->nullable()->change();
            
              // Reapply the foreign key with 'nullable' behavior
              $table->foreign('notify_to')->references('id')->on('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
             // Revert the nullable change
             $table->unsignedBigInteger('notify_to')->nullable(false)->change();

             // Revert the foreign key constraint
             $table->foreign('notify_to')->references('id')->on('branches')->onDelete('cascade');
        });
    }
};
