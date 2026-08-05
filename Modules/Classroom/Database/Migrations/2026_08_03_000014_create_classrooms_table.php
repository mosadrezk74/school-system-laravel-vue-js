<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->smallInteger('capacity')->default(30);
            $table->tinyInteger('floor')->nullable();
            $table->string('building', 50)->nullable();
            $table->string('room_type', 20)->default('classroom');
            $table->boolean('has_projector')->default(false);
            $table->boolean('has_smartboard')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique('code');
            $table->index(['branch_id', 'is_active']);
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
