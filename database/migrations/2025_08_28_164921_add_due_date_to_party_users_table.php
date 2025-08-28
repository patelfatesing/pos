<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('use_credit');
        });
    }

    public function down(): void
    {
        Schema::table('party_users', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
};
