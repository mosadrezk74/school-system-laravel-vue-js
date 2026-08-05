<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('student_id');
            $table->string('relationship', 50);
            $table->boolean('is_primary_contact')->default(true);
            $table->boolean('can_pickup')->default(true);
            $table->boolean('receives_notifications')->default(true);
            $table->timestamps();
            $table->unique(['parent_id', 'student_id']);
            $table->index(['student_id']);
            $table->foreign('parent_id')->references('id')->on('parent_profiles')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');
    }
};
