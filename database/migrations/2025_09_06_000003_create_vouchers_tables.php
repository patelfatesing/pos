<?php
// database/migrations/2025_09_06_000003_create_vouchers_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vouchers', function (Blueprint $t) {
            $t->id();
            $t->date('voucher_date');
            $t->enum('voucher_type', ['Journal','Payment','Receipt','Contra','Sales','Purchase','DebitNote','CreditNote']);
            $t->string('ref_no')->nullable();
            $t->foreignId('branch_id')->nullable()->constrained('branches');
            $t->text('narration')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
            $t->index(['voucher_date','voucher_type']);
        });

        Schema::create('voucher_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $t->foreignId('ledger_id')->constrained('account_ledgers')->cascadeOnDelete();
            $t->enum('dc', ['Dr','Cr']);
            $t->decimal('amount', 15, 2);
            $t->text('line_narration')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('voucher_lines');
        Schema::dropIfExists('vouchers');
    }
};
