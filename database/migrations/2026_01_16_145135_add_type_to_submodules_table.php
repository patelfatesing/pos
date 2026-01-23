<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToSubmodulesTable extends Migration
{
    public function up()
    {
        Schema::table('submodules', function (Blueprint $table) {

            // Add the new ENUM column
            $table->enum('type', ['add', 'edit', 'delete', 'list', 'all'])
                  ->default('list')
                  ->after('slug');
        });
    }

    public function down()
    {
        Schema::table('submodules', function (Blueprint $table) {
            // Drop the column if rollback
            $table->dropColumn('type');
        });
    }
}
