<?php

// database/migrations/2025_09_13_000001_create_modules_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $t) {
            $t->id();
            $t->string('name');             // e.g. "Job Posts"
            $t->string('slug')->unique();   // e.g. "job_posts"
            $t->enum('is_active', ['yes', 'no'])->default('yes');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
