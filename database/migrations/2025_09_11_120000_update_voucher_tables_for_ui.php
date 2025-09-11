<?php

// database/migrations/2025_09_11_120000_update_voucher_tables_for_ui.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // --- vouchers
        Schema::table('vouchers', function (Blueprint $table) {
            // party/counter ledgers (nullable helpers for UX/reporting)
            if (!Schema::hasColumn('vouchers', 'party_ledger_id'))  $table->unsignedBigInteger('party_ledger_id')->nullable()->after('branch_id');
            if (!Schema::hasColumn('vouchers', 'mode'))             $table->enum('mode', ['cash','bank','upi','card'])->nullable()->after('party_ledger_id');
            if (!Schema::hasColumn('vouchers', 'instrument_no'))    $table->string('instrument_no', 50)->nullable()->after('mode');
            if (!Schema::hasColumn('vouchers', 'instrument_date'))  $table->date('instrument_date')->nullable()->after('instrument_no');
            if (!Schema::hasColumn('vouchers', 'cash_ledger_id'))   $table->unsignedBigInteger('cash_ledger_id')->nullable()->after('instrument_date');
            if (!Schema::hasColumn('vouchers', 'bank_ledger_id'))   $table->unsignedBigInteger('bank_ledger_id')->nullable()->after('cash_ledger_id');

            // contra helpers
            if (!Schema::hasColumn('vouchers', 'from_ledger_id'))   $table->unsignedBigInteger('from_ledger_id')->nullable()->after('bank_ledger_id');
            if (!Schema::hasColumn('vouchers', 'to_ledger_id'))     $table->unsignedBigInteger('to_ledger_id')->nullable()->after('from_ledger_id');

            // trade totals
            if (!Schema::hasColumn('vouchers', 'sub_total'))        $table->decimal('sub_total', 15, 2)->default(0)->after('to_ledger_id');
            if (!Schema::hasColumn('vouchers', 'discount'))         $table->decimal('discount', 15, 2)->default(0)->after('sub_total');
            if (!Schema::hasColumn('vouchers', 'tax'))              $table->decimal('tax', 15, 2)->default(0)->after('discount');
            if (!Schema::hasColumn('vouchers', 'grand_total'))      $table->decimal('grand_total', 15, 2)->default(0)->after('tax');

            // indexes that help common filters
            $table->index(['voucher_date']);
            $table->index(['voucher_type']);
            $table->index(['branch_id', 'voucher_type', 'voucher_date'], 'vouchers_branch_type_date_idx');

            // optional: avoid duplicate ref_no inside branch+type (NULLs allowed)
            $table->unique(['branch_id', 'voucher_type', 'ref_no'], 'vouchers_branch_type_ref_unique');
        });

        // --- voucher_lines
        Schema::table('voucher_lines', function (Blueprint $table) {
            $table->index(['voucher_id']);
            $table->index(['ledger_id']);
            $table->index(['dc']);
        });

        // --- Foreign keys (adjust table names to your actual ledgers/users/branches tables)
        Schema::table('vouchers', function (Blueprint $table) {
            // Assuming: account_ledgers(id), branches(id), users(id)
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            if (Schema::hasColumn('vouchers', 'branch_id'))       $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade')->onDelete('set null');
            if (Schema::hasColumn('vouchers', 'party_ledger_id')) $table->foreign('party_ledger_id')->references('id')->on('account_ledgers')->onUpdate('cascade')->onDelete('set null');
            if (Schema::hasColumn('vouchers', 'cash_ledger_id'))  $table->foreign('cash_ledger_id')->references('id')->on('account_ledgers')->onUpdate('cascade')->onDelete('set null');
            if (Schema::hasColumn('vouchers', 'bank_ledger_id'))  $table->foreign('bank_ledger_id')->references('id')->on('account_ledgers')->onUpdate('cascade')->onDelete('set null');
            if (Schema::hasColumn('vouchers', 'from_ledger_id'))  $table->foreign('from_ledger_id')->references('id')->on('account_ledgers')->onUpdate('cascade')->onDelete('set null');
            if (Schema::hasColumn('vouchers', 'to_ledger_id'))    $table->foreign('to_ledger_id')->references('id')->on('account_ledgers')->onUpdate('cascade')->onDelete('set null');
        });

        Schema::table('voucher_lines', function (Blueprint $table) {
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('ledger_id')->references('id')->on('account_ledgers')->onUpdate('cascade')->onDelete('restrict');
        });

        // --- Optional CHECKs (MySQL 8+). Comment out if your MySQL doesn't enforce CHECK.
        try {
            \DB::statement("ALTER TABLE voucher_lines ADD CONSTRAINT chk_vl_amount_pos CHECK (amount > 0)");
        } catch (\Throwable $e) { /* ignore if unsupported */ }
    }

    public function down(): void
    {
        // rollback: drop FKs and columns (keep it minimal)
        Schema::table('voucher_lines', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropForeign(['ledger_id']);
            $table->dropIndex(['voucher_id']);
            $table->dropIndex(['ledger_id']);
            $table->dropIndex(['dc']);
            // cannot reliably drop CHECK in all envs; ignore
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropUnique('vouchers_branch_type_ref_unique');
            $table->dropIndex('vouchers_branch_type_date_idx');
            $table->dropIndex(['voucher_date']);
            $table->dropIndex(['voucher_type']);

            $drops = [
                'party_ledger_id','mode','instrument_no','instrument_date',
                'cash_ledger_id','bank_ledger_id','from_ledger_id','to_ledger_id',
                'sub_total','discount','tax','grand_total'
            ];
            foreach ($drops as $col) { if (Schema::hasColumn('vouchers', $col)) $table->dropColumn($col); }

            // created_by / branch_id FKs (created_by existed before—keep if you want)
            if (Schema::hasColumn('vouchers','created_by')) $table->dropForeign(['created_by']);
            if (Schema::hasColumn('vouchers','branch_id'))  $table->dropForeign(['branch_id']);
        });
    }
};

