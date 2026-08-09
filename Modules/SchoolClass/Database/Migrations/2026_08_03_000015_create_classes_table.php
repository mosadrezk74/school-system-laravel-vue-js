<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_level_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('classroom_id')->nullable();
            $table->string('name', 100);
            $table->smallInteger('capacity')->default(30);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['academic_year_id', 'grade_level_id']);
            $table->foreign('grade_level_id')->references('id')->on('grade_levels')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
