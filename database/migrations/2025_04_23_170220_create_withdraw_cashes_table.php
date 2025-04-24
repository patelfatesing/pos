<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('withdraw_cashes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('cash_break_id');
            $table->decimal('amount', 10, 2);
            $table->text('note')->nullable();
            $table->timestamps();
    
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('cash_break_id')->references('id')->on('cash_breakdowns')->onDelete('cascade');
        });
    }
    
};
