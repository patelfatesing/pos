<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_type_id')->nullable()->after('id');

        });
    }

    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropForeign(['expense_type_id']);
            $table->dropColumn('expense_type_id');
        });
    }
};
