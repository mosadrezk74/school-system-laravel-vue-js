<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('current_class_id')->nullable();

            $table->string('admission_number', 50)->nullable();
            $table->string('roll_number', 50)->nullable();
            $table->date('admission_date');

            $table->string('emergency_contact_name', 255)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relation', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();

            $table->string('father_name', 255)->nullable();
            $table->string('father_phone', 20)->nullable();
            $table->string('mother_name', 255)->nullable();
            $table->string('mother_phone', 20)->nullable();
            $table->string('guardian_name', 255)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_relation', 50)->nullable();

            $table->string('blood_group', 5)->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_conditions')->nullable();

            $table->string('previous_school', 255)->nullable();
            $table->decimal('scholarship_percentage', 5, 2)->default(0);

            $table->boolean('is_graduated')->default(false);
            $table->boolean('is_active')->default(true);

            $table->json('extra_fields')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
            $table->unique('admission_number');
            $table->index('current_class_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            // Not-null column: cascading keeps every profile pointing at a real
            // year instead of leaving an orphaned row a nullOnDelete would need.
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('current_class_id')->references('id')->on('classes')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
