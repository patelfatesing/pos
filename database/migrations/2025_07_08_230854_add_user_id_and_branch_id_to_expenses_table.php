<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdAndBranchIdToExpensesTable extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('description');
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_id');

            // Optionally, add foreign keys:
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'branch_id']);
        });
    }
}
