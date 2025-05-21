<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductImagePathToImagesTables extends Migration
{
    public function up()
    {
        Schema::table('party_images', function (Blueprint $table) {
            $table->string('product_image_path')->nullable()->after('image_path');
        });

        Schema::table('commission_user_images', function (Blueprint $table) {
            $table->string('product_image_path')->nullable()->after('image_path');
        });
    }

    public function down()
    {
        Schema::table('party_images', function (Blueprint $table) {
            $table->dropColumn('product_image_path');
        });

        Schema::table('commission_user_images', function (Blueprint $table) {
            $table->dropColumn('product_image_path');
        });
    }
}
