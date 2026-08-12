<?php

namespace Modules\News\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\News\Enums\NewsAudience;
use Modules\News\Enums\NewsStatus;

class UpdateNewsRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('title'),
            ...$this->translatableRules('body'),

            'audience' => ['required', 'string', Rule::in(NewsAudience::values())],
            'status' => ['required', 'string', Rule::in(NewsStatus::values())],
            'published_at' => ['nullable', 'date'],
            'is_pinned' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => __('news::news.fields.title'),
            'body' => __('news::news.fields.body'),
            'audience' => __('news::news.fields.audience'),
            'status' => __('news::news.fields.status'),
            'published_at' => __('news::news.fields.published_at'),
        ];
    }
}
