<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('account_groups', function (Blueprint $t) {
            // who created this group (nullable for system/default)
            $t->foreignId('created_by')->nullable()
              ->after('is_primary')->constrained('users')->nullOnDelete();

            // true = user created; false = system/built-in
            $t->boolean('is_user_defined')->default(true)->after('created_by');

            // optional: soft deletes (uncomment if you want)
            // $t->softDeletes();
        });

        // Backfill: mark existing primary groups as system (not user-defined)
        DB::table('account_groups')->where('is_primary', true)->update(['is_user_defined' => false]);
    }

    public function down(): void
    {
        Schema::table('account_groups', function (Blueprint $t) {
            // if you added softDeletes above, drop it first:
            // $t->dropSoftDeletes();
            $t->dropConstrainedForeignId('created_by');
            $t->dropColumn('is_user_defined');
        });
    }
};
