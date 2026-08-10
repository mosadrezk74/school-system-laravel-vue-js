<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the obsolete Department domain from databases that were migrated
 * before it was dropped.
 *
 * Fresh installs never build any of this — the departments table migration was
 * removed and teacher_profiles no longer declares department_id — so every step
 * here is guarded and simply no-ops on a clean database.
 *
 * down() is intentionally empty: Department is gone for good, and recreating a
 * table that no forward migration builds would leave a fresh database with
 * schema it should not have.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('teacher_profiles', 'department_id')) {
            Schema::table('teacher_profiles', function (Blueprint $table) {
                $foreignKeys = collect(Schema::getForeignKeys('teacher_profiles'))
                    ->filter(fn (array $key): bool => in_array('department_id', $key['columns'], true))
                    ->pluck('name');

                foreach ($foreignKeys as $foreignKey) {
                    $table->dropForeign($foreignKey);
                }

                $indexes = collect(Schema::getIndexes('teacher_profiles'))
                    ->filter(fn (array $index): bool => in_array('department_id', $index['columns'], true))
                    ->pluck('name');

                foreach ($indexes as $index) {
                    $table->dropIndex($index);
                }

                $table->dropColumn('department_id');
            });
        }

        Schema::dropIfExists('departments');
    }

    public function down(): void
    {
        // Department is permanently removed; nothing to restore.
    }
};
