<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('grade_level_id')->nullable()->after('max_amount');
            $table->unsignedBigInteger('classroom_id')->nullable()->after('grade_level_id');
            $table->foreign('grade_level_id')->references('id')->on('grade_levels')->nullOnDelete();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
        });

        Schema::table('fee_discounts', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });

        Schema::table('fee_discounts', function (Blueprint $table) {
            $table->dropColumn('class_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_discounts', function (Blueprint $table) {
            $table->dropForeign(['grade_level_id']);
            $table->dropForeign(['classroom_id']);
            $table->dropColumn(['grade_level_id', 'classroom_id']);
        });

        Schema::table('fee_discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('class_id')->nullable()->after('max_amount');
            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
        });
    }
};
