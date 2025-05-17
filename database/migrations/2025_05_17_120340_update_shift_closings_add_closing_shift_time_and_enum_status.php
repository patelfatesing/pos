<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateShiftClosingsAddClosingShiftTimeAndEnumStatus extends Migration
{
    public function up(): void
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->timestamp('closing_shift_time')->nullable()->after('end_time');
            $table->enum('status', ['pending', 'completed', 'cancelled', 'closing'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('shift_closings', function (Blueprint $table) {
            $table->dropColumn('closing_shift_time');

            // If you want to revert the ENUM to its previous type (e.g., string), do this:
            $table->string('status')->default('pending')->change();
        });
    }
}
