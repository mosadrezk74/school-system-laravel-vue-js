<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behavior_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->json('name');
            $table->string('type', 10);
            $table->smallInteger('points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_categories');
    }
};
