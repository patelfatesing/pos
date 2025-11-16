<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** ---- helpers ---- */
    private function indexExists(string $table, string $index): bool
    {
        return collect(DB::select(
            "SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1",
            [$table, $index]
        ))->isNotEmpty();
    }

    private function fkExists(string $table, string $fkName): bool
    {
        return collect(DB::select(
            "SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE='FOREIGN KEY' LIMIT 1",
            [$table, $fkName]
        ))->isNotEmpty();
    }

    public function up(): void
    {
        // ------- vouchers: add columns first (as you already do) -------
        Schema::table('vouchers', function (Blueprint $table) {
            // (keep your addColumn logic with Schema::hasColumn checks)
            // ...
        });

        // ------- vouchers: indexes / uniques (guarded) -------
        Schema::table('vouchers', function (Blueprint $table) {
            // name your indexes explicitly so you can check them:
            if (!$this->indexExists('vouchers', 'vouchers_voucher_date_index')) {
                $table->index('voucher_date', 'vouchers_voucher_date_index');
            }
            if (!$this->indexExists('vouchers', 'vouchers_voucher_type_index')) {
                $table->index('voucher_type', 'vouchers_voucher_type_index');
            }
            if (!$this->indexExists('vouchers', 'vouchers_branch_type_date_idx')) {
                $table->index(['branch_id','voucher_type','voucher_date'], 'vouchers_branch_type_date_idx');
            }
            if (!$this->indexExists('vouchers', 'vouchers_branch_type_ref_unique')) {
                $table->unique(['branch_id','voucher_type','ref_no'], 'vouchers_branch_type_ref_unique');
            }
        });

        // ------- voucher_lines: indexes (guarded) -------
        Schema::table('voucher_lines', function (Blueprint $table) {
            if (!$this->indexExists('voucher_lines', 'voucher_lines_voucher_id_index')) {
                $table->index('voucher_id', 'voucher_lines_voucher_id_index');
            }
            if (!$this->indexExists('voucher_lines', 'voucher_lines_ledger_id_index')) {
                $table->index('ledger_id', 'voucher_lines_ledger_id_index');
            }
            if (!$this->indexExists('voucher_lines', 'voucher_lines_dc_index')) {
                $table->index('dc', 'voucher_lines_dc_index');
            }
        });

        // ------- foreign keys (guarded) -------
        Schema::table('vouchers', function (Blueprint $table) {
            if (!$this->fkExists('vouchers', 'vouchers_created_by_foreign')) {
                $table->foreign('created_by', 'vouchers_created_by_foreign')
                      ->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            }
            if (Schema::hasColumn('vouchers','branch_id') && !$this->fkExists('vouchers','vouchers_branch_id_foreign')) {
                $table->foreign('branch_id', 'vouchers_branch_id_foreign')
                      ->references('id')->on('branches')->onUpdate('cascade')->onDelete('set null');
            }
            foreach ([
                'party_ledger_id' => 'vouchers_party_ledger_id_foreign',
                'cash_ledger_id'  => 'vouchers_cash_ledger_id_foreign',
                'bank_ledger_id'  => 'vouchers_bank_ledger_id_foreign',
                'from_ledger_id'  => 'vouchers_from_ledger_id_foreign',
                'to_ledger_id'    => 'vouchers_to_ledger_id_foreign',
            ] as $col => $fkName) {
                if (Schema::hasColumn('vouchers',$col) && !$this->fkExists('vouchers', $fkName)) {
                    $table->foreign($col, $fkName)
                          ->references('id')->on('account_ledgers')->onUpdate('cascade')->onDelete('set null');
                }
            }
        });

        Schema::table('voucher_lines', function (Blueprint $table) {
            if (!$this->fkExists('voucher_lines','voucher_lines_voucher_id_foreign')) {
                $table->foreign('voucher_id', 'voucher_lines_voucher_id_foreign')
                      ->references('id')->on('vouchers')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!$this->fkExists('voucher_lines','voucher_lines_ledger_id_foreign')) {
                $table->foreign('ledger_id', 'voucher_lines_ledger_id_foreign')
                      ->references('id')->on('account_ledgers')->onUpdate('cascade')->onDelete('restrict');
            }
        });

        // Optional CHECK
        try {
            DB::statement("ALTER TABLE voucher_lines ADD CONSTRAINT chk_vl_amount_pos CHECK (amount > 0)");
        } catch (\Throwable $e) { /* ignore if exists/unsupported */ }
    }

    public function down(): void
    {
        // drop FKs if exist
        Schema::table('voucher_lines', function (Blueprint $table) {
            foreach (['voucher_lines_voucher_id_foreign','voucher_lines_ledger_id_foreign'] as $fk) {
                try { $table->dropForeign($fk); } catch (\Throwable $e) {}
            }
            foreach (['voucher_lines_voucher_id_index','voucher_lines_ledger_id_index','voucher_lines_dc_index'] as $idx) {
                try { $table->dropIndex($idx); } catch (\Throwable $e) {}
            }
        });

        Schema::table('vouchers', function (Blueprint $table) {
            foreach ([
                'vouchers_created_by_foreign',
                'vouchers_branch_id_foreign',
                'vouchers_party_ledger_id_foreign',
                'vouchers_cash_ledger_id_foreign',
                'vouchers_bank_ledger_id_foreign',
                'vouchers_from_ledger_id_foreign',
                'vouchers_to_ledger_id_foreign',
            ] as $fk) { try { $table->dropForeign($fk); } catch (\Throwable $e) {} }

            foreach ([
                'vouchers_branch_type_ref_unique',
                'vouchers_branch_type_date_idx',
                'vouchers_voucher_date_index',
                'vouchers_voucher_type_index',
            ] as $idx) { try { $table->dropIndex($idx); } catch (\Throwable $e) {} }

            // (drop added columns if you want; keep your existing hasColumn checks)
        });
    }
};
