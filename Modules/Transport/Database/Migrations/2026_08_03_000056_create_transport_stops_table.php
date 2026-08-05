<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id');
            $table->string('name', 255);
            $table->tinyInteger('sequence');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
            $table->unique(['route_id', 'sequence']);
            $table->foreign('route_id')->references('id')->on('transport_routes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_stops');
    }
};
