<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records a refund as a financial event only. The school moves the money
     * itself, so nothing here triggers a transfer; the row is the audit trail
     * for a decision an admin already made.
     */
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('amount', 10, 2);
            $table->date('refund_date');
            $table->string('refund_method', 20)->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['payment_id']);
            $table->index(['refund_date']);
            $table->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('student_profiles')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
