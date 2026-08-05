<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('stop_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->time('pickup_time')->nullable();
            $table->time('dropoff_time')->nullable();
            $table->decimal('fee', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id']);
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('route_id')->references('id')->on('transport_routes')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->cascadeOnDelete();
            $table->foreign('stop_id')->references('id')->on('transport_stops')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_assignments');
    }
};
