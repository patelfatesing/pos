<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pack_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('size');
            $table->unsignedBigInteger('sub_category_id');
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->enum('is_deleted', ['yes', 'no'])->default('no');
            $table->timestamps();

            $table->foreign('sub_category_id')
                  ->references('id')
                  ->on('sub_categories')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_sizes');
    }
};

