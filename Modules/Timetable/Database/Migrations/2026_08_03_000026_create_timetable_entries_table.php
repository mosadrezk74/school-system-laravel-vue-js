<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timetable_id');
            $table->tinyInteger('day_of_week');
            $table->tinyInteger('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('classroom_id')->nullable();
            $table->boolean('is_break')->default(false);
            $table->timestamps();
            $table->unique(['timetable_id', 'day_of_week', 'period_number']);
            $table->index(['teacher_id']);
            $table->index(['classroom_id']);
            $table->foreign('timetable_id')->references('id')->on('timetables')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
