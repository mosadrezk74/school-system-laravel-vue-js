<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_subject', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_profile_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();
            $table->unique(['teacher_profile_id', 'subject_id']);
            $table->index(['subject_id']);
            $table->foreign('teacher_profile_id')->references('id')->on('teacher_profiles')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subject');
    }
};
