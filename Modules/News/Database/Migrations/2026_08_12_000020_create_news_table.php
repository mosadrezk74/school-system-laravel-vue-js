<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * School news.
 *
 * Deliberately separate from broadcast_messages: that table records a dispatch
 * (sender, sent_at, sent_count, failed_count) and was reshaped for internal
 * messaging. News is a persistent notice board people come back and read, so
 * it needs its own lifecycle — draft, scheduled publication, pinning — none of
 * which a send-once record models.
 *
 * No cover image column: spatie/laravel-medialibrary is installed but no model
 * in the project uses it yet, so adding one here would mean introducing upload
 * infrastructure rather than reusing it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();

            // Spatie-translatable JSON keyed by locale, as everywhere else.
            $table->json('title');
            $table->json('body');

            /*
             * Derived from the title, kept unique across live rows so a news
             * item has a stable readable identifier independent of its id.
             */
            $table->string('slug', 180)->unique();

            $table->string('status', 20)->default('draft');
            $table->string('audience', 20)->default('all');

            /*
             * Nullable so a draft carries no date. A published item with a
             * future date stays hidden until it arrives, which is what lets an
             * admin write ahead of an announcement.
             */
            $table->timestamp('published_at')->nullable();

            $table->boolean('is_pinned')->default(false);
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // The portal reads exactly this combination on every request.
            $table->index(['status', 'audience', 'published_at']);
            $table->index('is_pinned');

            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
