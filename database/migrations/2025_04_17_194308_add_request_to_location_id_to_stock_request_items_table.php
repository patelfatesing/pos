<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_request_items', function (Blueprint $table) {
            $table->foreignId('request_to_location_id')
                  ->nullable() // optional, remove if required
                  ->constrained('branches')
                  ->onDelete('set null'); // or 'cascade' if you prefer
        });
    }

    public function down(): void
    {
        Schema::table('stock_request_items', function (Blueprint $table) {
            $table->dropForeign(['request_to_location_id']);
            $table->dropColumn('request_to_location_id');
        });
    }
};
