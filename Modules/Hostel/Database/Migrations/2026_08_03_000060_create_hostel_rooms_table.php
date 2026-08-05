<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hostel_id');
            $table->string('room_number', 50);
            $table->tinyInteger('capacity');
            $table->tinyInteger('current_occupancy')->default(0);
            $table->string('room_type', 15)->default('double');
            $table->decimal('monthly_fee', 8, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['hostel_id', 'room_number']);
            $table->foreign('hostel_id')->references('id')->on('hostels')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};
