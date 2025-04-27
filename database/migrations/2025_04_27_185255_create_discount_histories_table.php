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
        Schema::create('discount_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('discount_amount', 8, 2); // Amount of discount applied
            $table->string('description')->nullable(); // Optional description for the discount
            $table->timestamps();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Assuming you're linking to a user table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_histories');
    }
};
