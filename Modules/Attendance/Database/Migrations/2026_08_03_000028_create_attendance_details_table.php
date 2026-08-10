<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_record_id');
            $table->unsignedBigInteger('student_id');
            $table->string('status', 15);
            $table->timestamps();
            $table->unique(['attendance_record_id', 'student_id']);
            $table->index(['student_id']);
            $table->index(['status']);
            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
    }
};
