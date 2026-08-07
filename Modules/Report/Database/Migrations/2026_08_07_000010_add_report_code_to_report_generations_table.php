<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_generations', function (Blueprint $table) {
            $table->string('report_code', 100)->nullable()->after('report_template_id');
            $table->index(['report_code']);
        });

        Schema::table('report_generations', function (Blueprint $table) {
            $table->dropForeign(['report_template_id']);
        });

        Schema::table('report_generations', function (Blueprint $table) {
            $table->unsignedBigInteger('report_template_id')->nullable()->change();
        });

        Schema::table('report_generations', function (Blueprint $table) {
            $table->foreign('report_template_id')->references('id')->on('report_templates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('report_generations', function (Blueprint $table) {
            $table->dropForeign(['report_template_id']);
        });

        Schema::table('report_generations', function (Blueprint $table) {
            $table->unsignedBigInteger('report_template_id')->nullable(false)->change();
        });

        Schema::table('report_generations', function (Blueprint $table) {
            $table->foreign('report_template_id')->references('id')->on('report_templates')->cascadeOnDelete();
            $table->dropIndex(['report_code']);
            $table->dropColumn('report_code');
        });
    }
};
