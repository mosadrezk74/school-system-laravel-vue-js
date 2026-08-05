<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('fee_category_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('grade_level_id')->nullable();
            $table->string('name', 100);
            $table->decimal('amount', 10, 2);
            $table->string('fee_type', 20)->nullable();
            $table->string('frequency', 15);
            $table->boolean('is_optional')->default(false);
            $table->tinyInteger('due_day')->nullable();
            $table->decimal('late_fee_amount', 10, 2)->nullable();
            $table->tinyInteger('late_fee_days')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['academic_year_id', 'class_id', 'fee_category_id']);
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('fee_category_id')->references('id')->on('fee_categories')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
            $table->foreign('grade_level_id')->references('id')->on('grade_levels')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
