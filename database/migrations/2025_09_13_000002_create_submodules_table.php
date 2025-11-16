<?php

// database/migrations/2025_09_13_000002_create_submodules_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('submodules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $t->string('name');           // e.g. "Vouchers"
            $t->string('slug');           // e.g. "vouchers" (unique within the module)
            $t->enum('is_active',['yes','no'])->default('yes');
            $t->timestamps();
            $t->unique(['module_id','slug']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('submodules');
    }
};
