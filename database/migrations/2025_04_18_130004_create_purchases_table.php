<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_purchases_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no');
            $table->foreignId('vendor_id')->constrained('vendor_lists');
            $table->string('parchase_ledger');
            $table->decimal('total', 15, 2)->default(0);
            $table->date('date');

            $table->decimal('excise_fee', 15, 2)->default(0);
            $table->decimal('composition_vat', 15, 2)->default(0);
            $table->decimal('surcharge_on_ca', 15, 2)->default(0);
            $table->decimal('tcs', 15, 2)->default(0);
            $table->decimal('aed_to_be_paid', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->string('status')->default('pending');

            $table->timestamps();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};

