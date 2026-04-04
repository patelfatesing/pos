<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_breakdowns', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_id')->nullable()->after('branch_id');

            // Optional: add foreign key if you have shifts table
            // $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cash_breakdowns', function (Blueprint $table) {
            // Optional: drop foreign key first if added
            // $table->dropForeign(['shift_id']);

            $table->dropColumn('shift_id');
        });
    }
};