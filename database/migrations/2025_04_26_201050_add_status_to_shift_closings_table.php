<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToShiftClosingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            // Add status column with enum type
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            // Remove status column
            $table->dropColumn('status');
        });
    }
}
