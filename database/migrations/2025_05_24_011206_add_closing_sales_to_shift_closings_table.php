<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddClosingSalesToShiftClosingsTable extends Migration
{
    public function up(): void
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->json('closing_sales')->after('cash')->nullable();
        });

        // Add CHECK constraint for valid JSON (only works in MySQL 8+)
        DB::statement("ALTER TABLE `shift_closings` ADD CONSTRAINT `shift_closings_chk_2` CHECK (JSON_VALID(`closing_sales`))");
    }

    public function down(): void
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn('closing_sales');
        });
    }
}

