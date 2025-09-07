<?php

// database/migrations/2025_09_06_000001_create_account_groups_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('account_groups', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('code')->nullable();                 // optional short code
            $t->enum('nature', ['Asset','Liability','Income','Expense']); // BS / PL bucket
            $t->boolean('affects_gross')->default(false);   // for direct vs indirect (P&L)
            $t->foreignId('parent_id')->nullable()->constrained('account_groups')->nullOnDelete();
            $t->boolean('is_primary')->default(false);      // Tally primary groups
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('account_groups'); }
};
