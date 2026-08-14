<?php

use Illuminate\Support\Facades\Route;
use Modules\Communication\Http\Controllers\ConversationController;
use Modules\Communication\Http\Controllers\ConversationMessageController;
use Modules\Communication\Http\Controllers\EmailBroadcastController;
use Modules\Communication\Http\Controllers\MessageAttachmentController;
use Modules\Communication\Http\Controllers\MessageController;

Route::middleware(['web', 'auth', 'module:Communication'])->prefix('communication')->name('communication.')->group(function () {
    Route::resource('messages', MessageController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('email-broadcast', [EmailBroadcastController::class, 'create'])->name('emailBroadcast.create');
    Route::post('email-broadcast', [EmailBroadcastController::class, 'store'])->name('emailBroadcast.store');

    /*
     * Chat. Reads are GET, writes are POST, and there is no destroy route
     * because the domain has no "delete a message" behaviour yet — adding one
     * would be a policy decision, not a routing one.
     *
     * Attachments hang off their message rather than off the conversation, so
     * the pair in the URL is always self-consistent and the controller can
     * verify the media actually belongs to that message.
     */
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ConversationController::class, 'index'])->name('index');
        Route::post('conversations', [ConversationController::class, 'store'])->name('conversations.store');
        Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('show');

        Route::post('conversations/{conversation}/messages', [ConversationMessageController::class, 'store'])
            ->name('messages.store');
        Route::post('conversations/{conversation}/read', [ConversationMessageController::class, 'markRead'])
            ->name('read');

        Route::get('messages/{message}/attachments/{media}/download', [MessageAttachmentController::class, 'download'])
            ->name('attachments.download');
        Route::get('messages/{message}/attachments/{media}/preview', [MessageAttachmentController::class, 'preview'])
            ->name('attachments.preview');
    });
});
