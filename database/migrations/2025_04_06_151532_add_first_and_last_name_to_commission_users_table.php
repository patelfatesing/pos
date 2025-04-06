<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commission_users', function (Blueprint $table) {
            $table->string('first_name')->after('id')->nullable();
            $table->string('middle_name')->after('first_name')->nullable();
            $table->string('last_name')->after('middle_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('commission_users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name','last_name']);
        });
    }
};

