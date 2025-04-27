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
        Schema::create('credit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->integer('total_purchase_items');
            $table->enum('status', ['paid', 'unpaid'])->default('unpaid');
            $table->foreignId('party_user_id')->nullable()->constrained('party_users')->nullOnDelete();
            $table->foreignId('store_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_histories');
    }
};
