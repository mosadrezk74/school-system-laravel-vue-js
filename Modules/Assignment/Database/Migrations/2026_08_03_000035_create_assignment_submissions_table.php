<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamp('submitted_at');
            $table->decimal('score', 6, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('is_late')->default(false);
            $table->boolean('is_graded')->default(false);
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
            $table->index(['student_id']);
            $table->foreign('assignment_id')->references('id')->on('assignments')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
