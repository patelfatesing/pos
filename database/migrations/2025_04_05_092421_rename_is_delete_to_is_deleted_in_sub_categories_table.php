<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `sub_categories` CHANGE `is_delete` `is_deleted` ENUM('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `sub_categories` CHANGE `is_deleted` `is_delete` ENUM('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no'");
    }
};
