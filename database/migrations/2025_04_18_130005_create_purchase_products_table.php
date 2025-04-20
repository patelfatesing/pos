<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_purchase_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->integer('sr_no');
            $table->string('brand_name');
            $table->string('batch');
            $table->date('mfg_date');
            $table->decimal('mrp', 10, 2);
            $table->integer('qnt');
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_products');
    }
};
