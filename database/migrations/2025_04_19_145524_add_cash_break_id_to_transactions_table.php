<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('cash_break_id')->after('party_user_id')->nullable();

            $table->foreign('cash_break_id')
                ->references('id')
                ->on('cash_breakdowns')
                ->onDelete('set null'); // Or 'cascade', 'restrict', etc.
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['cash_break_id']);
            $table->dropColumn('cash_break_id');
        });
        
    }
};


