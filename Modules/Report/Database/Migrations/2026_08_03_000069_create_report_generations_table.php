<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_generations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_template_id');
            $table->json('filters_used')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->string('format', 10)->nullable();
            $table->string('status', 15)->default('pending');
            $table->text('file_url')->nullable();
            $table->timestamps();
            $table->index(['report_template_id']);
            $table->index(['created_at']);
            $table->foreign('report_template_id')->references('id')->on('report_templates')->cascadeOnDelete();
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_generations');
    }
};
