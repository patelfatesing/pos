<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_sub_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                  ->constrained('account_groups')
                  ->onDelete('cascade');  // delete sub groups if parent deleted
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('nature', ['Asset','Liability','Income','Expense']);
            $table->boolean('affects_gross')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['group_id','name']); // prevent duplicates under same parent
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_sub_groups');
    }
};

