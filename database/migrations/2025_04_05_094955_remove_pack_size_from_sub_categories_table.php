<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn('pack_sise');
        });
    }

    public function down(): void
    {
        Schema::table('sub_categories', function (Blueprint $table) {
            $table->string('pack_sise')->nullable(); // Add back if needed
        });
    }
};

