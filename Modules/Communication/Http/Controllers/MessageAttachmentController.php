<?php

namespace Modules\Communication\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Modules\Communication\Exceptions\ConversationOperationException;
use Modules\Communication\Models\Message;
use Modules\Communication\Services\MessageAttachmentService;
use Modules\Core\Abstracts\BaseInertiaController;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The only way to read a message attachment.
 *
 * Attachments live on a private disk and are never given a public URL, so this
 * controller is the whole access surface. Every request re-derives the answer
 * from the database rather than trusting anything in the URL:
 *
 *   1. `{message}` is resolved by route binding — a real message or a 404.
 *   2. `{media}` must belong to *that* message and to the attachment
 *      collection. A media id from another model, another message, or another
 *      collection is rejected here, which is what closes the IDOR: guessing a
 *      neighbouring media id gets you nothing unless it hangs off a message
 *      you can already read.
 *   3. The conversation policy must pass, so membership — not merely holding
 *      `message.view` — decides.
 *
 * The path is read from the media row rather than from the request, so there
 * is no client-supplied string anywhere in the file lookup for a traversal
 * sequence to live in.
 */
class MessageAttachmentController extends BaseInertiaController
{
    public function __construct(
        private readonly MessageAttachmentService $attachments,
    ) {}

    public function download(Message $message, Media $media): StreamedResponse
    {
        $attachment = $this->authorizeAttachment($message, $media);

        return Storage::disk($attachment->disk)->download(
            $attachment->getPathRelativeToRoot(),
            $attachment->file_name,
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
                /*
                 * Stops a browser from second-guessing the declared type and
                 * rendering, say, a text/plain file as HTML.
                 */
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=0, no-store',
            ],
        );
    }

    /**
     * Inline rendering, offered only for the types the module has decided are
     * safe to hand to the browser (images).
     *
     * Anything else falls back to a download, so an attacker who stores an
     * HTML or SVG payload cannot get it executed on this origin — the stored
     * XSS route a blanket `inline` disposition would open.
     */
    public function preview(Message $message, Media $media): StreamedResponse
    {
        $attachment = $this->authorizeAttachment($message, $media);

        if (! $this->attachments->isPreviewable($attachment)) {
            return $this->download($message, $media);
        }

        return Storage::disk($attachment->disk)->response(
            $attachment->getPathRelativeToRoot(),
            $attachment->file_name,
            [
                'Content-Type' => $attachment->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                /*
                 * A private file must not be held by a shared proxy, and the
                 * browser copy must not outlive the reader's access to it.
                 */
                'Cache-Control' => 'private, max-age=0, no-store',
            ],
        );
    }

    /**
     * Re-reads the media row through the message's own collection, so the
     * route binding cannot be satisfied by a media id the caller merely
     * guessed.
     *
     * @throws ConversationOperationException
     */
    private function authorizeAttachment(Message $message, Media $media): Media
    {
        $attachment = $message->getMedia($message->attachmentCollection())
            ->firstWhere('id', $media->id);

        if (! $attachment instanceof Media) {
            throw ConversationOperationException::attachmentNotFound();
        }

        $conversation = $message->conversation;

        if ($conversation === null) {
            throw ConversationOperationException::attachmentNotFound();
        }

        $this->authorize('downloadAttachment', $conversation);

        if (! Storage::disk($attachment->disk)->exists($attachment->getPathRelativeToRoot())) {
            throw ConversationOperationException::attachmentMissingFromDisk();
        }

        return $attachment;
    }
}
