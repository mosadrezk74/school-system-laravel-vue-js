<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->string('type', 10);
            $table->unsignedBigInteger('warden_id')->nullable();
            $table->smallInteger('total_capacity')->default(0);
            $table->smallInteger('current_occupancy')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique('code');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('warden_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostels');
    }
};
