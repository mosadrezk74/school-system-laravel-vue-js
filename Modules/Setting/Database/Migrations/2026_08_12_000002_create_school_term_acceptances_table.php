<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who agreed to which version of the school's terms, and when.
 *
 * The row is the evidence, so it is never soft-deleted: an acceptance either
 * happened or it did not. Deleting the user cascades, since the record has no
 * meaning without the person it belongs to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_term_acceptances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_term_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('accepted_at');
            $table->timestamps();

            /*
             * The database is the last word on "accept once per version" —
             * a double-submit or two concurrent requests both reach here, and
             * one of them must fail rather than write a second row.
             */
            $table->unique(['school_term_id', 'user_id']);

            // Answers "has this user accepted the current version?" directly.
            $table->index(['user_id', 'school_term_id']);

            $table->foreign('school_term_id')->references('id')->on('school_terms')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_term_acceptances');
    }
};
