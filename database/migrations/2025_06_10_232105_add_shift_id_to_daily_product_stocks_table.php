<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_id')->after('branch_id');

            // If shift_id has a foreign key relationship, uncomment below line
            // $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('daily_product_stocks', function (Blueprint $table) {
            // If foreign key exists, drop it first
            // $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });
    }
};
