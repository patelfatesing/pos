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
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('is_deleted', ['yes', 'no'])
            ->default('no')
            ->collation('utf8mb4_unicode_ci')
            ->after('is_delete'); // optional: change column order

        $table->dropColumn('is_delete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('is_delete', ['yes', 'no'])
            ->default('no')
            ->collation('utf8mb4_unicode_ci')
            ->after('is_deleted');

        $table->dropColumn('is_deleted');
        });
    }
};
