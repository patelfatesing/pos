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
        Schema::create('branch', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('address')->nullable(); // Another VARCHAR column (optional)
            $table->text('description')->nullable(); // Another VARCHAR column (optional)
            $table->enum('is_active', ['yes', 'no'])->default('yes'); // ENUM column
            $table->enum('is_deleted', ['yes', 'no'])->default('no'); // ENUM column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch');
    }
};
