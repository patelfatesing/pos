<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInOutEnableToBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            // Adding the in_out_enable field as a boolean
            $table->boolean('in_out_enable')->default(0)->after('is_deleted'); // Positioning after is_deleted
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            // Dropping the in_out_enable field
            $table->dropColumn('in_out_enable');
        });
    }
}
