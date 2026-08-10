<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('type', 20);
            $table->string('payment_url')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('iban', 64)->nullable();
            $table->json('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'display_order']);
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
