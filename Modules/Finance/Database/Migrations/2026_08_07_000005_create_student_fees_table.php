<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->string('status', 15)->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['student_id', 'fee_structure_id']);
            $table->index(['status']);
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
