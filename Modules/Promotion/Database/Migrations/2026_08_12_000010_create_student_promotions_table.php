<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The end-of-year decision taken for each student, and the audit trail of it.
 *
 * student_profiles holds only where a student is *now*, so promoting one
 * overwrites their previous year with no record of it. This table keeps both
 * sides of every move, which makes the history readable and makes a revert
 * possible without guessing the prior state.
 *
 * Deliberately carries no semester_id: promotion is a year-to-year decision,
 * and nothing in the existing domain ties it to a term. It carries no
 * financial columns either — this phase is academic only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();

            // The student identifier is student_profiles.id, never users.id.
            $table->unsignedBigInteger('student_id');

            $table->unsignedBigInteger('from_academic_year_id');

            /*
             * Null for retained, graduated and transferred: those decisions
             * do not move the student into another year, and storing the
             * source year again would misread as a move to itself.
             */
            $table->unsignedBigInteger('to_academic_year_id')->nullable();

            // Null when the student had no class, or is not being moved.
            $table->unsignedBigInteger('from_class_id')->nullable();
            $table->unsignedBigInteger('to_class_id')->nullable();

            $table->string('status', 20);

            /*
             * Records whether the student was graduated *before* this
             * decision, so a revert can restore the exact prior state rather
             * than assuming it was false.
             */
            $table->boolean('was_graduated')->default(false);

            $table->unsignedBigInteger('promoted_by')->nullable();
            $table->timestamp('promoted_at');
            $table->text('remarks')->nullable();
            $table->timestamps();

            /*
             * A student is decided once per source year. Re-running a bulk
             * promotion therefore fails loudly at the database rather than
             * silently writing a second, conflicting history row.
             *
             * Keyed on the *source* year rather than the target because
             * retained/graduated/transferred rows have no target year, and
             * MySQL treats NULLs as distinct in a unique index — which would
             * let the same student be retained repeatedly.
             */
            $table->unique(['student_id', 'from_academic_year_id'], 'student_promotions_student_from_year_unique');

            $table->index('student_id');
            $table->index('from_academic_year_id');
            $table->index('to_academic_year_id');
            $table->index('status');

            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('from_academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('to_academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('from_class_id')->references('id')->on('classes')->nullOnDelete();
            $table->foreign('to_class_id')->references('id')->on('classes')->nullOnDelete();
            $table->foreign('promoted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
