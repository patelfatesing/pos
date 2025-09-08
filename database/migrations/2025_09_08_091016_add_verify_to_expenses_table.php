<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // If you want Yes/No as enum:
            $table->enum('verify', ['Yes', 'No'])->default('Yes')->after('branch_id');

            // Or if you prefer tinyint (1/0):
            // $table->boolean('verify')->default(true)->after('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('verify');
        });
    }
};
