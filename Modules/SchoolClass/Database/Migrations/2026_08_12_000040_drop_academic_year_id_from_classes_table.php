<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Classes stop belonging to an academic year.
 *
 * The school runs standing classes — "Grade 6-A", "Sec 3-B" — that recur every
 * year rather than being recreated for each one, so pinning them to a single
 * year was wrong for this domain. Records that genuinely are year-bound
 * (assignments, exams, attendance, timetables, promotions) keep their own
 * academic_year_id; only the class itself becomes timeless.
 *
 * Uniqueness moves with it: a class name only has to be unique within its
 * grade level now, not within a grade level *and* a year.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
         * Order matters and each step is its own statement.
         *
         * MySQL refuses to drop classes_year_grade_name_unique while the
         * academic_year_id foreign key still relies on it for lookups
         * (error 1553), so the constraint has to go first. Batching these into
         * one ALTER lets Laravel emit them in the wrong order.
         */
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique('classes_year_grade_name_unique');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('academic_year_id');

            // A section name still has to be unique inside its grade level.
            $table->unique(['grade_level_id', 'name'], 'classes_grade_name_unique');
        });
    }

    /**
     * Restores the column so the change is reversible in structure. The
     * original per-class year values cannot be recovered — nothing else
     * records them — so every class is put back under the current academic
     * year, which is where all of them already sat.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique('classes_grade_name_unique');
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('grade_level_id');
        });

        $currentYearId = DB::table('academic_years')->where('is_current', true)->value('id')
            ?? DB::table('academic_years')->orderBy('id')->value('id');

        if ($currentYearId !== null) {
            DB::table('classes')->whereNull('academic_year_id')->update(['academic_year_id' => $currentYearId]);
        }

        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable(false)->change();
            $table->unique(['academic_year_id', 'grade_level_id', 'name'], 'classes_year_grade_name_unique');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
        });
    }
};
