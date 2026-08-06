<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->after('teacher_id');
            $table->unsignedBigInteger('school_class_id')->nullable()->after('subject_id');
            $table->unsignedBigInteger('timetable_session_id')->nullable()->after('school_class_id');

            $table->index(['subject_id']);
            $table->index(['school_class_id']);
            $table->unique(['timetable_session_id']);

            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('school_class_id')->references('id')->on('classes')->nullOnDelete();
            $table->foreign('timetable_session_id')->references('id')->on('timetable_sessions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['school_class_id']);
            $table->dropForeign(['timetable_session_id']);
            $table->dropColumn(['subject_id', 'school_class_id', 'timetable_session_id']);
        });
    }
};
