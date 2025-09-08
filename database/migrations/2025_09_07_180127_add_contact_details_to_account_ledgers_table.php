<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('account_ledgers', function (Blueprint $t) {
            // Place it where you prefer. If you already added is_deleted, put after it; otherwise remove ->after()
            $t->text('contact_details')->nullable()->after('is_deleted'); // or ->after('is_active')
        });
    }

    public function down(): void
    {
        Schema::table('account_ledgers', function (Blueprint $t) {
            $t->dropColumn('contact_details');
        });
    }
};
