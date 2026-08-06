<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('short_name', 50)->nullable();
            $table->string('subject_type', 20)->default('core');
            $table->decimal('pass_grade', 5, 2)->default(60);
            $table->tinyInteger('credit_hours')->default(1);
            $table->decimal('max_marks', 6, 2)->default(100);
            $table->string('color', 7)->default('#2563eb');
            $table->string('icon', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('code');
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
