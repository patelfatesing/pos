<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commission_user_images', function (Blueprint $table) {
            $table->string('type')->after('id'); // You can change 'id' to any column to place after it
        });
    }

    public function down(): void
    {
        Schema::table('commission_user_images', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
