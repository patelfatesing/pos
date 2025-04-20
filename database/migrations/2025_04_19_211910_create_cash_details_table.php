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
        Schema::create('cash_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_closing_id')->constrained()->onDelete('cascade');
            $table->integer('denomination');
            $table->integer('quantity');
            $table->integer('total');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_details');
    }
};
