<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateRolePermissionsAccessEnum extends Migration
{
    public function up()
    {
        /*
         |----------------------------------------------------------
         | STEP 1: Normalize existing values (safety step)
         |----------------------------------------------------------
         */
        DB::table('role_permissions')
            ->where('access', 'yes')
            ->update(['access' => 'yes']);

        DB::table('role_permissions')
            ->where('access', 'no')
            ->update(['access' => 'no']);

        DB::table('role_permissions')
            ->where('access', 'all')
            ->update(['access' => 'all']);

        /*
         |----------------------------------------------------------
         | STEP 2: Extend ENUM to include own & none
         |----------------------------------------------------------
         */
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->enum('access', ['none', 'own', 'no', 'yes', 'all'])
                  ->default('no')
                  ->change();
        });
    }

    public function down()
    {
        /*
         |----------------------------------------------------------
         | STEP 1: Convert unsupported values back
         |----------------------------------------------------------
         */
        DB::table('role_permissions')
            ->whereIn('access', ['none', 'own'])
            ->update(['access' => 'no']);

        /*
         |----------------------------------------------------------
         | STEP 2: Restore original ENUM
         |----------------------------------------------------------
         */
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->enum('access', ['all', 'yes', 'no'])
                  ->default('no')
                  ->change();
        });
    }
}
