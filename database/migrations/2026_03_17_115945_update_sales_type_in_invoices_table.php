<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE invoices 
            MODIFY sales_type ENUM('normal','one_time','admin_sale') 
            NOT NULL DEFAULT 'normal'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE invoices 
            MODIFY sales_type ENUM('normal','one_time') 
            NOT NULL DEFAULT 'normal'
        ");
    }
};