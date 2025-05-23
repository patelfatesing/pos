<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('credit_collections', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('party_user_id');
            $table->foreign('party_user_id')->references('id')->on('party_users')->onDelete('cascade');

            $table->decimal('amount', 12, 2);

            $table->unsignedBigInteger('collected_by');
            $table->foreign('collected_by')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('cash_break_id')->nullable();
            // Uncomment below if cash_breaks table exists
            // $table->foreign('cash_break_id')->references('id')->on('cash_breaks')->onDelete('set null');

            $table->json('note_data')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_collections');
    }
};
