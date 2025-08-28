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
        Schema::create('expense_main_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();                 // Direct, Indirect, etc.
            $table->enum('type', ['direct', 'indirect'])->index(); // canonical label
            $table->string('slug')->unique();                 // direct, indirect (optional but handy)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_main_categories');
    }
};
