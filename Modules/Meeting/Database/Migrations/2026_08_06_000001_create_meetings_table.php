<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('title', 255);
            $table->json('description')->nullable();
            $table->string('room_name', 100)->unique();
            $table->string('meeting_url', 255);
            $table->string('password', 100)->nullable();
            $table->timestamp('scheduled_at');
            $table->unsignedInteger('duration_minutes');
            $table->string('status', 20)->default('scheduled');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['teacher_id']);
            $table->index(['academic_year_id', 'semester_id']);
            $table->index(['status']);
            $table->index(['scheduled_at']);
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('semester_id')->references('id')->on('semesters')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
