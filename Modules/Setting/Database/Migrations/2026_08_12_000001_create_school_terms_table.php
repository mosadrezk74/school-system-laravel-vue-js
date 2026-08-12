<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The school's terms and conditions, kept as an immutable version history.
 *
 * A published version is never edited: families accept a specific wording, so
 * changing that text underneath them would invalidate the record of what they
 * agreed to. Editing means drafting the next version instead.
 *
 * Deliberately carries no academic_year_id or semester_id — the school's terms
 * are an institutional policy, not an academic-context record, and they stay
 * in force across year boundaries until replaced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_terms', function (Blueprint $table) {
            $table->id();

            // Spatie-translatable JSON, keyed by locale, like every other
            // translatable column in the schema.
            $table->json('title');
            $table->json('content');

            $table->unsignedInteger('version');
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            /*
             * Version numbers are unique across live rows so two concurrent
             * drafts cannot claim the same number. Soft-deleted rows keep
             * their number too, which is intended: a discarded draft does not
             * free its slot and history stays gap-tolerant but unambiguous.
             */
            $table->unique('version');

            $table->index('status');
            $table->index('published_at');

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_terms');
    }
};
