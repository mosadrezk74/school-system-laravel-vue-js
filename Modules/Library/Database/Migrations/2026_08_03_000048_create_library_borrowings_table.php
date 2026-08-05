<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_borrowings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('library_item_id');
            $table->unsignedBigInteger('borrower_id');
            $table->string('borrower_type', 10)->nullable();
            $table->date('borrowing_date');
            $table->date('due_date');
            $table->date('actual_return_date')->nullable();
            $table->smallInteger('quantity')->default(1);
            $table->string('status', 15)->nullable();
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['library_item_id', 'borrower_id', 'status']);
            $table->index(['due_date']);
            $table->foreign('library_item_id')->references('id')->on('library_items')->cascadeOnDelete();
            $table->foreign('borrower_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_borrowings');
    }
};
