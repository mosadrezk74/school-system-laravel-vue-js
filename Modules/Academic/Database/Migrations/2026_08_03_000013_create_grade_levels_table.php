<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->json('name');
            $table->json('description')->nullable();
            $table->smallInteger('display_order')->default(0);
            $table->tinyInteger('min_age')->nullable();
            $table->tinyInteger('max_age')->nullable();
            $table->decimal('passing_grade', 5, 2)->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['code']);
            $table->index(['display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};
