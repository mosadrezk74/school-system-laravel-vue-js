<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The column already exists on installations that predate this file, but no
     * migration ever created it — so a fresh database (including the test one)
     * came up without it and every scholarship silently read as zero. Guarded
     * so it is a no-op where the column is already present.
     */
    public function up(): void
    {
        if (Schema::hasColumn('student_profiles', 'scholarship_percentage')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            /*
             * No `after()` anchor: the columns it would follow are themselves
             * absent on a freshly migrated database, so pinning the position
             * fails there. Column order carries no meaning here.
             */
            $table->decimal('scholarship_percentage', 5, 2)->default(0);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('student_profiles', 'scholarship_percentage')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('scholarship_percentage');
        });
    }
};
