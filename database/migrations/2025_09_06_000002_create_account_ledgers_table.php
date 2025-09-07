<?php
// database/migrations/2025_09_06_000002_create_account_ledgers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_ledgers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->foreignId('group_id')->constrained('account_groups')->cascadeOnDelete();
            $t->foreignId('branch_id')->nullable()->constrained('branches'); // if you have branches
            $t->decimal('opening_balance', 15, 2)->default(0);
            $t->enum('opening_type', ['Dr', 'Cr'])->default('Dr');
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->unique(['name', 'branch_id']); // same ledger name allowed per branch
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('account_ledgers');
    }
};
