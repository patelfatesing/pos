<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pack_sizes', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            $table->dropForeign(['sub_category_id']); // safe if you're re-adding

            // Make column nullable
            $table->unsignedBigInteger('sub_category_id')->nullable()->change();

            // Add foreign key constraint
            $table->foreign('sub_category_id')
                  ->references('id')
                  ->on('sub_categories')
                  ->nullOnDelete(); // Optional: set null on delete
        });
    }

    public function down(): void
    {
        Schema::table('pack_sizes', function (Blueprint $table) {
            $table->dropForeign(['sub_category_id']);
            $table->unsignedBigInteger('sub_category_id')->nullable(false)->change(); // revert to NOT NULL
        });
    }
};
