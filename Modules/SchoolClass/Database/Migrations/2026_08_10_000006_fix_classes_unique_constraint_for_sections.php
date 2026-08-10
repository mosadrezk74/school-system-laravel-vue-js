<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A grade level runs several sections in the same year — 3-A, 3-B, 3-C —
     * but the original index allowed only one class per grade level per year.
     * The name is what distinguishes one section from another, so it belongs
     * in the key.
     *
     * Only the unique index changes. Column names, foreign keys and every
     * `class_id` pointing at this table are left exactly as they are.
     */
    public function up(): void
    {
        /*
         * Order matters. The old index is the only one leading with
         * academic_year_id, so InnoDB is using it to satisfy that foreign key
         * and refuses to drop it while it stands alone. Creating the
         * replacement first — which starts with the same column — gives the
         * constraint somewhere else to point before the old one goes.
         */
        Schema::table('classes', function (Blueprint $table) {
            $table->unique(['academic_year_id', 'grade_level_id', 'name'], 'classes_year_grade_name_unique');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique('classes_academic_year_id_grade_level_id_unique');
        });
    }

    public function down(): void
    {
        // Reverses in the same order, for the same foreign-key reason.
        Schema::table('classes', function (Blueprint $table) {
            $table->unique(['academic_year_id', 'grade_level_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique('classes_year_grade_name_unique');
        });
    }
};
