<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->integer('worked_minutes')->nullable();
            $table->string('status', 20);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
            $table->index(['status']);
            $table->foreign('employee_id')->references('id')->on('staff_profiles')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendance');
    }
};
