<?php

namespace Modules\Communication\Transformers;

use Illuminate\Http\Request;
use Modules\Communication\Services\MessageAttachmentService;
use Modules\Core\Abstracts\BaseResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One attachment as the chat renders it.
 *
 * There is deliberately no storage path, disk name or public URL in this
 * payload. The only address the client is given is the module's own download
 * route, which re-checks conversation membership on every hit — publishing a
 * direct URL would make that check optional.
 *
 * @mixin Media
 */
class MessageAttachmentResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $previewable = app(MessageAttachmentService::class)->isPreviewable($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => (int) $this->size,

            /*
             * Whether the browser may render this inline. Driven by the
             * server's allowlist rather than by the client sniffing the mime,
             * so the two can never disagree about what is safe to display.
             */
            'is_previewable' => $previewable,

            'download_url' => route('communication.chat.attachments.download', [
                'message' => $this->model_id,
                'media' => $this->id,
            ]),

            'preview_url' => $previewable
                ? route('communication.chat.attachments.preview', [
                    'message' => $this->model_id,
                    'media' => $this->id,
                ])
                : null,
        ];
    }
}
