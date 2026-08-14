<?php

namespace Modules\Communication\Services;

use Illuminate\Http\UploadedFile;
use Modules\Communication\Models\Message;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Moves validated uploads onto a message, and answers the questions the
 * resource layer asks about the resulting files.
 *
 * File handling is concentrated here rather than in the Action so that the
 * Action stays a description of the write, and rather than in the controller
 * because a controller that touches UploadedFile ends up owning storage
 * decisions it cannot test.
 */
class MessageAttachmentService
{
    /**
     * Attach every uploaded file to the message.
     *
     * Runs inside the caller's transaction. Media Library writes the file to
     * disk as it goes, so a rollback leaves the row gone but the bytes behind:
     * that is an orphaned file the `media:clean` command reclaims, and is the
     * right trade against the alternative of a message row pointing at a file
     * that was never written.
     *
     * @param  array<int, UploadedFile>  $files
     */
    public function attach(Message $message, array $files): void
    {
        foreach ($files as $file) {
            $message
                ->addMedia($file)
                ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->usingFileName($this->safeFileName($file))
                ->toMediaCollection($message->attachmentCollection());
        }
    }

    public function isPreviewable(Media $media): bool
    {
        return in_array(
            (string) $media->mime_type,
            (array) config('communication.attachments.previewable', []),
            true,
        );
    }

    /**
     * A stored name that cannot be used to traverse or to smuggle a second
     * extension.
     *
     * The original name is kept for display (`media.name`); this is only what
     * lands on disk. Media Library sanitises too, but the extension is pinned
     * from the validated client extension here so "report.pdf.exe" cannot
     * survive as an executable name on the filesystem.
     */
    private function safeFileName(UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $slug = preg_replace('/[^A-Za-z0-9\p{Arabic}_-]+/u', '-', $base) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'attachment';
        }

        return mb_substr($slug, 0, 80).'.'.$extension;
    }
}
