<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('name', 255);
            $table->string('code', 50)->nullable();
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->string('unit', 15)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->integer('reorder_level')->default(0);
            $table->integer('current_stock')->default(0);
            $table->string('stock_status', 15)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique('code');
            $table->unique(['sku']);
            $table->index(['category_id']);
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('inventory_categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
