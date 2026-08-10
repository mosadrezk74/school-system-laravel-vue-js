<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ties a fee structure to the academic period it covers, so a "Term 1
     * 2026/2027" subscription can be expressed without a parallel plans table.
     * The period's duration is read from the semester's own dates rather than
     * stored here, so no subscription length is ever hardcoded.
     */
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('fee_category_id');
            $table->unsignedBigInteger('semester_id')->nullable()->after('academic_year_id');
            $table->boolean('is_active')->default(true)->after('is_optional');

            $table->index(['academic_year_id', 'semester_id'], 'fee_structures_period_index');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('semester_id')->references('id')->on('semesters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['semester_id']);
            $table->dropIndex('fee_structures_period_index');
            $table->dropColumn(['academic_year_id', 'semester_id', 'is_active']);
        });
    }
};
