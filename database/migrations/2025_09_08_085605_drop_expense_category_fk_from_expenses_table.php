<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['expense_category_id']); 
            // If you also want to drop the column itself:
            // $table->dropColumn('expense_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Re-add the foreign key
            $table->foreign('expense_category_id')
                  ->references('id')->on('expense_categories')
                  ->cascadeOnDelete();
        });
    }
};
