<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected array $tables = [
        'branches',
        'categories',
        'commission_users',
        'demand_orders',
        'expenses',
        'pack_sizes',
        'party_users',
        'sub_categories',
        'vendor_lists',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {

                if (!Schema::hasColumn($table->getTable(), 'created_by')) {
                    $table->foreignId('created_by')
                        ->nullable()
                        ->after('updated_at')
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn($table->getTable(), 'updated_by')) {
                    $table->foreignId('updated_by')
                        ->nullable()
                        ->after('created_by')
                        ->constrained('users')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {

                if (Schema::hasColumn($table->getTable(), 'updated_by')) {
                    $table->dropForeign([$table->getTable().'_updated_by_foreign']);
                    $table->dropColumn('updated_by');
                }

                if (Schema::hasColumn($table->getTable(), 'created_by')) {
                    $table->dropForeign([$table->getTable().'_created_by_foreign']);
                    $table->dropColumn('created_by');
                }
            });
        }
    }
};
