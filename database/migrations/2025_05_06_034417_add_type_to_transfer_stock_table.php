<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `notifications` MODIFY `type` ENUM('low_stock','request_stock','approved_stock','price_change','others','expire_product','transfer_stock') NOT NULL DEFAULT 'others'");
    }

    public function down(): void
    {
         // You can revert it back to previous type if needed. For example:
            DB::statement("ALTER TABLE `notifications` MODIFY `type` VARCHAR(255) NOT NULL");
    }
};