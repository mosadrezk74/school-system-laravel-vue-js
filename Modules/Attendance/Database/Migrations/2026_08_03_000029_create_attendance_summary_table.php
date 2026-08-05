<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->unsignedBigInteger('class_id');
            $table->smallInteger('total_days')->default(0);
            $table->smallInteger('present_days')->default(0);
            $table->smallInteger('absent_days')->default(0);
            $table->smallInteger('late_days')->default(0);
            $table->decimal('attendance_percentage', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id', 'class_id']);
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('semester_id')->references('id')->on('semesters')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summary');
    }
};
