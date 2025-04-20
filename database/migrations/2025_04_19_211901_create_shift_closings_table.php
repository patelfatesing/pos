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
        Schema::create('shift_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');   // references users table
            $table->foreignId('branch_id')->constrained()->onDelete('cascade'); // references branches table

            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('opening_cash')->default(0);

            $table->integer('deshi_sales')->default(0);
            $table->integer('beer_sales')->default(0);
            $table->integer('english_sales')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('upi_payment')->default(0);
            $table->integer('withdrawal_payment')->default(0);

            $table->json('cash')->nullable(); // denomination breakdown
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_closings');
    }
};
