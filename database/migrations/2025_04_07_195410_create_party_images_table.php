<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('party_user_id'); // Link to party_users table
            $table->string('image_path'); // Store image path or filename
            $table->string('type')->nullable(); // Optional: e.g. 'profile', 'document', etc.
            $table->timestamps();

            // Foreign key constraint (optional if party_users table exists)
           // $table->foreign('party_user_id')->references('id')->on('party_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_images');
    }
};
