<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('employee_id', 50)->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('job_title', 255)->nullable();
            $table->string('employment_type', 20)->nullable();
            $table->date('joining_date')->nullable();
            $table->string('salary_type', 20)->nullable();
            $table->decimal('salary_base', 10, 2)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('user_id');
            $table->unique('employee_id');
            $table->index(['department_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
