<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionIdToPartyImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('party_images', function (Blueprint $table) {
            // Add transaction_id column
            $table->unsignedBigInteger('transaction_id')->nullable()->after('party_user_id');
            
            // Add foreign key constraint
            $table->foreign('transaction_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('party_images', function (Blueprint $table) {
            // Drop the foreign key and column
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
    }
}

