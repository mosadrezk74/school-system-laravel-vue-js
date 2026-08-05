<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('title', 255);
            $table->json('authors');
            $table->string('publisher', 100)->nullable();
            $table->string('publication_year')->nullable();
            $table->string('language', 20)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('shelf_location', 50)->nullable();
            $table->string('item_type', 15)->nullable();
            $table->smallInteger('quantity')->default(1);
            $table->smallInteger('available_quantity')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('fine_per_day', 6, 2)->nullable();
            $table->tinyInteger('max_borrow_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique('isbn');
            $table->index(['category_id']);
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('library_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_items');
    }
};
