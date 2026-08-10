<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Models\ErrorReport;

/**
 * @mixin ErrorReport
 */
class ErrorReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'level' => $this->level,
            'error_code' => $this->error_code,
            'status_code' => $this->status_code,
            'exception' => $this->exception,
            'exception_short' => class_basename($this->exception),
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'route' => $this->route,
            'method' => $this->method,
            'url' => $this->url,
            'request_id' => $this->request_id,
            'occurrences' => $this->occurrences,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'context' => $this->context,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ]),
            'resolver' => $this->whenLoaded('resolver', fn () => [
                'id' => $this->resolver?->id,
                'name' => $this->resolver?->name,
            ]),
            'first_seen_at' => $this->first_seen_at?->toISOString(),
            'last_seen_at' => $this->last_seen_at?->toISOString(),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
