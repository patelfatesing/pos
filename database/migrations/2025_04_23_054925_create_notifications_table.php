<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['low_stock', 'request_stock', 'approved_stock', 'price_change', 'others']);
            $table->string('content');
            $table->text('details')->nullable();
            $table->unsignedBigInteger('notify_to');
            $table->enum('status', ['read', 'unread'])->default('unread');
            $table->tinyInteger('priority')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('notify_to')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
