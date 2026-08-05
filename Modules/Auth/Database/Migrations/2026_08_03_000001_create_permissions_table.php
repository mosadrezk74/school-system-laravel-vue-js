<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('guard_name', 50)->default('web');
            $table->string('module', 50)->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
            $table->index(['module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
