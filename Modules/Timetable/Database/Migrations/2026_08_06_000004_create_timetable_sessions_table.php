<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timetable_entry_id');
            $table->date('session_date');
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();
            $table->unique(['timetable_entry_id', 'session_date']);
            $table->index(['session_date']);
            $table->index(['status']);
            $table->foreign('timetable_entry_id')->references('id')->on('timetable_entries')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_sessions');
    }
};
