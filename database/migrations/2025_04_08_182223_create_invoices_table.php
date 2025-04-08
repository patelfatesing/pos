<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();

            // Foreign keys
            $table->foreignId('commission_user_id')
                ->nullable()
                ->constrained('commission_users')
                ->nullOnDelete();

            $table->foreignId('party_user_id')
                ->nullable()
                ->constrained('party_users')
                ->nullOnDelete();

            $table->json('items');
            $table->decimal('sub_total', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            $table->decimal('party_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);

            // Example additional column
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

