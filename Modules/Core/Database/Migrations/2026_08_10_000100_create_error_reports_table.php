<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operational record of unexpected exceptions, surfaced in the admin Error
 * Center. Laravel's own logging is untouched — this is an extra sink that
 * exists purely so admins can filter, group and resolve errors in the UI.
 *
 * Identical failures collapse onto one row via `fingerprint`, so a crash in a
 * loop cannot flood the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_reports', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 64);
            $table->string('status', 20)->default('new');
            $table->string('level', 20)->default('error');
            $table->string('error_code', 50)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('exception', 255);
            $table->text('message');
            $table->string('file', 1024)->nullable();
            $table->unsignedInteger('line')->nullable();
            $table->string('route', 255)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('request_id', 64)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->longText('trace')->nullable();
            $table->json('context')->nullable();
            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamps();

            $table->unique('fingerprint');
            $table->index(['status']);
            $table->index(['level']);
            $table->index(['last_seen_at']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_reports');
    }
};
