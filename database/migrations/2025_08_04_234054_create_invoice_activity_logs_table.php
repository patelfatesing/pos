<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceActivityLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('action'); // add, update, remove, credit_change
            $table->text('description')->nullable(); // Human-readable note
            $table->json('old_data')->nullable();    // Old data for update/remove/credit
            $table->json('new_data')->nullable();    // New data for update/add/credit
            $table->unsignedBigInteger('user_id')->nullable(); // who made change
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_activity_logs');
    }
}

