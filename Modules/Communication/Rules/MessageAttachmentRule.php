<?php

namespace Modules\Communication\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * The one place a chat attachment is judged acceptable.
 *
 * Three independent checks have to agree, because each is defeatable alone:
 *
 *   upload state  — a truncated or failed upload never reaches storage
 *   extension     — closed list; what the filesystem and the OS will act on
 *   mime          — closed list, read from the file's own bytes rather than
 *                   from the Content-Type the browser claimed
 *
 * The last point is why `getMimeType()` is used instead of
 * `getClientMimeType()`: a client can label `payload.php` as `image/png` and
 * a mime-only check would pass it. Requiring the guessed mime *and* the
 * extension to both be whitelisted means a renamed executable fails on
 * extension and a mislabelled one fails on mime.
 */
class MessageAttachmentRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail(__('communication::chat.validation.attachment_invalid'));

            return;
        }

        if (! $value->isValid()) {
            $fail(__('communication::chat.validation.attachment_upload_failed'));

            return;
        }

        $extension = strtolower((string) $value->getClientOriginalExtension());

        if (! in_array($extension, self::allowedExtensions(), true)) {
            $fail(__('communication::chat.validation.attachment_extension', [
                'extension' => $extension !== '' ? $extension : '—',
            ]));

            return;
        }

        /*
         * Guessed from the file's contents by Symfony, not taken from the
         * request headers. A file whose bytes do not match anything known
         * returns null, which is treated as "not on the list".
         */
        $mime = (string) $value->getMimeType();

        if (! in_array($mime, self::allowedMimes(), true)) {
            $fail(__('communication::chat.validation.attachment_mime'));

            return;
        }

        $maxKb = self::maxSizeKb();

        if ($value->getSize() > $maxKb * 1024) {
            $fail(__('communication::chat.validation.attachment_size', ['max' => $maxKb]));
        }
    }

    /**
     * @return array<int, string>
     */
    public static function allowedExtensions(): array
    {
        return (array) config('communication.attachments.extensions', []);
    }

    /**
     * @return array<int, string>
     */
    public static function allowedMimes(): array
    {
        return (array) config('communication.attachments.mimes', []);
    }

    public static function maxSizeKb(): int
    {
        return (int) config('communication.attachments.max_size_kb', 10240);
    }

    public static function maxPerMessage(): int
    {
        return (int) config('communication.attachments.max_per_message', 10);
    }

    /**
     * The `accept` attribute for the file picker. Advisory only — it filters
     * the OS dialog and is not a control; the rule above is.
     */
    public static function acceptAttribute(): string
    {
        return implode(',', array_map(
            static fn (string $extension): string => ".{$extension}",
            self::allowedExtensions(),
        ));
    }
}
