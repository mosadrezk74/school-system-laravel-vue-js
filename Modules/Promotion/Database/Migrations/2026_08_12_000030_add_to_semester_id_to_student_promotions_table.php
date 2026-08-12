<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The target semester an administrator promotes a student into.
 *
 * Additive only: the column is nullable because retained, graduated and
 * transferred decisions do not move a student into a new academic context and
 * so carry no target semester. A `promoted` decision requires one, which is
 * enforced in the request and again in the guard rather than by the column —
 * the same place the existing target-year and target-class rules live.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->unsignedBigInteger('to_semester_id')
                ->nullable()
                ->after('to_academic_year_id');

            $table->index('to_semester_id');

            // nullOnDelete matches to_academic_year_id / to_class_id: losing a
            // semester must not erase the promotion history that references it.
            $table->foreign('to_semester_id')
                ->references('id')
                ->on('semesters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->dropForeign(['to_semester_id']);
            $table->dropIndex(['to_semester_id']);
            $table->dropColumn('to_semester_id');
        });
    }
};
