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
        Schema::create('invoice_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('cash_amount', 10, 2)->default(0.00);
            $table->decimal('upi_amount', 10, 2)->default(0.00);
            $table->decimal('creditpay', 10, 2)->default(0.00);
            $table->string('payment_mode')->nullable();
            $table->string('invoice_number');
            $table->foreignId('commission_user_id')->nullable()->constrained('commission_users')->nullOnDelete();
            $table->foreignId('party_user_id')->nullable()->constrained('party_users')->nullOnDelete();
            $table->foreignId('cash_break_id')->nullable()->constrained('cash_breakdowns')->nullOnDelete();
            $table->json('items');
            $table->decimal('sub_total', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            $table->decimal('party_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('pending');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->string('change_type'); // created, updated, deleted
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('history_created_at')->useCurrent();

          //  DB::statement('ALTER TABLE invoice_histories ADD CONSTRAINT check_items_json CHECK (json_valid(items))');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_histories');
    }
};
