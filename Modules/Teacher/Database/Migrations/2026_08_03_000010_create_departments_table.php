<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->unsignedBigInteger('head_of_department_id')->nullable();
            $table->unsignedBigInteger('parent_department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique('code');
            $table->index(['parent_department_id']);
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('head_of_department_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('parent_department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
