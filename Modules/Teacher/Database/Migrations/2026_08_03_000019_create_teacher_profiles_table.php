<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('employee_id', 50)->nullable();
            $table->string('qualification', 255)->nullable();
            $table->string('specialization', 255)->nullable();
            $table->tinyInteger('experience_years')->default(0);
            $table->date('joining_date')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('position', 100)->nullable();
            $table->string('salary_type', 20)->nullable();
            $table->decimal('salary_base', 10, 2)->nullable();
            $table->json('primary_subjects')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('user_id');
            $table->unique('employee_id');
            $table->index(['department_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
