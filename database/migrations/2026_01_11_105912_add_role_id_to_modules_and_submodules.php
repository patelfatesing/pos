<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submodules', function (Blueprint $table) {
            // 1. Drop foreign key first
            $table->dropForeign('submodules_module_id_foreign');

            // 2. Then drop unique index
            $table->dropUnique('submodules_module_id_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('submodules', function (Blueprint $table) {
            // 1. Recreate unique index
            $table->unique(['module_id', 'slug'], 'submodules_module_id_slug_unique');

            // 2. Recreate foreign key
            $table->foreign('module_id')
                  ->references('id')
                  ->on('modules')
                  ->onDelete('cascade');
        });
    }
};
