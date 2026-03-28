<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_lists', function (Blueprint $table) {
            $table->enum('type', ['local', 'main'])
                  ->default('local')
                  ->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_lists', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};