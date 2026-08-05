<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name', 255);
            $table->string('school_code', 50);
            $table->string('school_email', 200)->nullable();
            $table->string('school_phone', 20)->nullable();
            $table->text('school_address')->nullable();
            $table->string('school_city', 100)->nullable();
            $table->string('school_country', 100)->nullable();
            $table->string('primary_color', 7)->default('#2563eb');
            $table->string('secondary_color', 7)->default('#10b981');
            $table->string('timezone', 50)->default('Asia/Riyadh');
            $table->string('currency', 10)->default('SAR');
            $table->string('currency_symbol', 10)->default('﷼');
            $table->string('default_locale', 10)->default('ar');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('school_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
