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
        Schema::create('invoice_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');

            $table->decimal('cash_amount', 10, 2)->default(0.00);
            $table->decimal('upi_amount', 10, 2)->default(0.00);
            $table->decimal('online_amount', 10, 2)->default(0.00);
            $table->decimal('creditpay', 10, 2)->default(0.00);
            $table->decimal('remaining_credit_pay', 10, 2)->default(0.00);
            $table->decimal('paid_credit', 10, 2)->default(0.00);
            $table->string('payment_mode')->nullable();
            $table->string('invoice_number');
            $table->string('ref_no')->nullable();

            $table->foreignId('commission_user_id')->nullable()->constrained('commission_users')->nullOnDelete();
            $table->foreignId('party_user_id')->nullable()->constrained('party_users')->nullOnDelete();
            $table->foreignId('cash_break_id')->nullable()->constrained('cash_breakdowns')->nullOnDelete();

            $table->longText('items');
            $table->integer('total_item_qty')->nullable();
            $table->decimal('total_item_total', 10, 2)->nullable();
            $table->decimal('sub_total', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            $table->decimal('party_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->decimal('roundof', 10, 2)->default(0.00);
            $table->decimal('change_amount', 10, 2)->nullable();

            $table->string('status')->default('pending');
            $table->string('invoice_status')->default('unpaid');
            $table->timestamp('hold_date')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->string('change_type'); // e.g., "created", "updated", "voided"
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('history_created_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_logs');
    }
};
