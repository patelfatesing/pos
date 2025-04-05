<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('name');
            $table->string('category')->nullable()->after('description');
            $table->string('brand')->nullable()->after('category');
            $table->string('sku')->unique()->nullable()->after('brand');
            $table->boolean('status')->default(1)->after('quantity'); // 1 = Active, 0 = Inactive
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'category', 'brand', 'sku', 'status']);
        });
    }
};
