<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('account_ledgers', function (Blueprint $t) {
            $t->boolean('is_deleted')->default(false)->after('is_active');
            $t->foreignId('updated_by')->nullable()
              ->after('is_deleted')
              ->constrained('users')->nullOnDelete();

            $t->index('is_deleted');
            $t->index('updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('account_ledgers', function (Blueprint $t) {
            $t->dropIndex(['is_deleted']);
            $t->dropIndex(['updated_by']);
            $t->dropConstrainedForeignId('updated_by');
            $t->dropColumn('is_deleted');
        });
    }
};
