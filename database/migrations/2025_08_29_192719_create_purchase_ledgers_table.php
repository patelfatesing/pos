<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // assumes vendors are stored in vendor_lists table with id PK
            $table->unsignedBigInteger('vendor_id');
            $table->enum('is_active', ['Yes', 'No'])->default('Yes');
            $table->timestamps();

            $table->foreign('vendor_id')
                  ->references('id')->on('vendor_lists')
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); // change to 'cascade' if you prefer
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_ledgers');
    }
};
