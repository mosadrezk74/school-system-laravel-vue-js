<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behavior_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('behavior_category_id');
            $table->date('date');
            $table->smallInteger('points');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('reported_by');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('status', 15)->default('reported');
            $table->timestamps();
            $table->index(['student_id']);
            $table->index(['date']);
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('behavior_category_id')->references('id')->on('behavior_categories')->cascadeOnDelete();
            $table->foreign('reported_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_records');
    }
};
