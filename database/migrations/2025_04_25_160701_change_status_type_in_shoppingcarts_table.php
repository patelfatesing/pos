<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeStatusTypeInShoppingcartsTable extends Migration
{
    public function up()
    {
        // Step 1: Rename old status column to status_old
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->renameColumn('status', 'status_old');
        });

        // Step 2: Add new status column as string
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('updated_at');
        });

        // Step 3: Convert int values to string equivalents
        DB::table('shoppingcarts')->where('status_old', 0)->update(['status' => 'pending']);
        DB::table('shoppingcarts')->where('status_old', 1)->update(['status' => 'hold']);
        DB::table('shoppingcarts')->where('status_old', 2)->update(['status' => 'completed']); // Add more as needed

        // Step 4: Drop old status column
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->dropColumn('status_old');
        });
    }

    public function down()
    {
        // Step 1: Add old status column back as integer
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->integer('status_old')->default(0)->after('updated_at');
        });

        // Step 2: Convert strings back to ints
        DB::table('shoppingcarts')->where('status', 'pending')->update(['status_old' => 0]);
        DB::table('shoppingcarts')->where('status', 'hold')->update(['status_old' => 1]);
        DB::table('shoppingcarts')->where('status', 'completed')->update(['status_old' => 2]); // Add more as needed

        // Step 3: Drop string column
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Step 4: Rename back to original
        Schema::table('shoppingcarts', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });
    }
}
