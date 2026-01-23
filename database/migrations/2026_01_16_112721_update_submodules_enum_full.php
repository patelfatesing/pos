<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateSubmodulesEnumFull extends Migration
{
    public function up()
    {
        // Step 1 — Convert old values to safe values first
        DB::table('submodules')
            ->where('is_active', 'yes')
            ->update(['is_active' => 'yes']);

        DB::table('submodules')
            ->where('is_active', 'no')
            ->update(['is_active' => 'no']);

        // Step 2 — Modify the ENUM to include new and old values
        Schema::table('submodules', function (Blueprint $table) {
            $table->enum('is_active', ['none', 'own', 'yes', 'no', 'all'])
                ->default('none')
                ->change();
        });
    }

    public function down()
    {
        // Convert unsupported values to safe options on rollback
        DB::table('submodules')
            ->whereIn('is_active', ['none','own','all'])
            ->update(['is_active' => 'no']);

        // Restore original ENUM
        Schema::table('submodules', function (Blueprint $table) {
            $table->enum('is_active', ['yes', 'no'])
                ->default('no')
                ->change();
        });
    }
}
