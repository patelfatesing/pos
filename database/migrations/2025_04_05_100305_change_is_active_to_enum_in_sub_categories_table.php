<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Optional: update existing values before altering column
        DB::statement("UPDATE sub_categories SET is_active = 'yes' WHERE is_active = '1'");
        DB::statement("UPDATE sub_categories SET is_active = 'no' WHERE is_active = '0'");

        // Alter column to ENUM('yes','no')
        DB::statement("ALTER TABLE sub_categories CHANGE is_active is_active ENUM('yes','no') NOT NULL DEFAULT 'yes'");
    }

    public function down(): void
    {
        // Revert back to 0/1 if needed
        DB::statement("UPDATE sub_categories SET is_active = '1' WHERE is_active = 'yes'");
        DB::statement("UPDATE sub_categories SET is_active = '0' WHERE is_active = 'no'");

        DB::statement("ALTER TABLE sub_categories CHANGE is_active is_active ENUM('0','1') NOT NULL DEFAULT '1'");
    }
};
