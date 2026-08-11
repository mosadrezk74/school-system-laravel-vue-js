<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            /*
             * Mirrors academic_years.is_current. At most one semester holds it
             * at a time; SemesterObserver enforces that invariant on write.
             */
            $table->boolean('is_current')->default(false)->after('is_active');
            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropIndex(['is_current']);
            $table->dropColumn('is_current');
        });
    }
};
