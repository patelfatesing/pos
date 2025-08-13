<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTypeEnumInNotificationsTable extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE `notifications` 
            MODIFY `type` ENUM(
                'low_stock',
                'request_stock',
                'approved_stock',
                'price_change',
                'others',
                'expire_product',
                'transfer_stock',
                'rejected_stock'
            ) DEFAULT 'low_stock'
        ");
    }

    public function down()
    {
        // Revert by removing 'rejected_stock'
        DB::statement("
            ALTER TABLE `notifications` 
            MODIFY `type` ENUM(
                'low_stock',
                'request_stock',
                'approved_stock',
                'price_change',
                'others',
                'expire_product',
                'transfer_stock'
            ) DEFAULT 'low_stock'
        ");
    }
}
