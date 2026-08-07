<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->unsignedBigInteger('classroom_id')->nullable()->after('grade_level_id');
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
        });

        DB::table('fee_structures')->update(['classroom_id' => null]);

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['class_id']);
            $table->dropUnique(['academic_year_id', 'class_id', 'fee_category_id']);
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn(['academic_year_id', 'class_id', 'fee_type']);
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->index(['fee_category_id', 'grade_level_id', 'classroom_id'], 'fee_structures_scope_index');
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropIndex('fee_structures_scope_index');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropColumn('classroom_id');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('id');
            $table->unsignedBigInteger('class_id')->nullable()->after('fee_category_id');
            $table->string('fee_type', 20)->nullable()->after('amount');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->unique(['academic_year_id', 'class_id', 'fee_category_id']);
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });
    }
};
