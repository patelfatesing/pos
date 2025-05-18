<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commission_users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('id'); // or 'name' or any other column
        });
    }

    public function down(): void
    {
        Schema::table('commission_users', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};

