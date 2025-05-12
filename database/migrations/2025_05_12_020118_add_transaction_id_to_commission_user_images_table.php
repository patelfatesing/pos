<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commission_user_images', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable()->after('commission_user_id');

            $table->foreign('transaction_id')
                  ->references('id')
                  ->on('invoices')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('commission_user_images', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
    }
};

