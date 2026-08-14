<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->unsignedBigInteger('semester_id')->nullable()->after('academic_year_id');

            $table->index(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table): void {
            $table->dropForeign(['semester_id']);
            $table->dropIndex(['semester_id']);
            $table->dropColumn('semester_id');
        });
    }
};
