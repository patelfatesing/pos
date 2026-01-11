<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSlugUniqueOnModulesAndSubmodules extends Migration
{
    public function up()
    {
        // Remove unique index from modules.slug
        Schema::table('modules', function (Blueprint $table) {
            // check if index exists before dropping
            try {
                $table->dropUnique('modules_slug_unique');
            } catch (\Exception $e) {
                // silent ignore if index doesn't exist
            }
        });

        // Remove unique index from submodules.slug
        Schema::table('submodules', function (Blueprint $table) {
            try {
                $table->dropUnique('submodules_slug_unique');
            } catch (\Exception $e) {
                // silent ignore
            }
        });
    }

    public function down()
    {
        // Re-add unique index (optional)
        Schema::table('modules', function (Blueprint $table) {
            $table->unique('slug', 'modules_slug_unique');
        });

        Schema::table('submodules', function (Blueprint $table) {
            $table->unique('slug', 'submodules_slug_unique');
        });
    }
}
