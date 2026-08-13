<?php

namespace Modules\News\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\News\Enums\NewsAudience;
use Modules\News\Enums\NewsStatus;

class UpdateNewsRequest extends BaseRequest
{
    /** The title feeds `news.slug`, a varchar(180), so it is kept comfortably under that. The body is an article. */
    private const TITLE_MAX = 150;

    private const BODY_MAX = 20000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('title', max: self::TITLE_MAX),
            ...$this->translatableRules('body', max: self::BODY_MAX),

            'audience' => ['required', 'string', Rule::enum(NewsAudience::class)],
            'status' => ['required', 'string', Rule::enum(NewsStatus::class)],
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
