<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->string('name', 100);
            $table->string('exam_type', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('passing_grade', 5, 2)->default(60);
            $table->decimal('total_marks', 6, 2)->default(100);
            $table->decimal('weightage', 4, 2)->default(1);
            $table->boolean('is_published')->default(false);
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['academic_year_id', 'semester_id']);
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('semester_id')->references('id')->on('semesters')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
