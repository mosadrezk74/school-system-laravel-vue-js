<?php

namespace Modules\Communication\Http\Requests;

use Modules\Communication\Services\RecipientResolverService;
use Modules\Core\Abstracts\BaseRequest;

class StoreMessageRequest extends BaseRequest
{
    /** `broadcast_messages.title`/`.body` are JSON, so these are domain limits. */
    private const TITLE_MAX = 150;

    private const BODY_MAX = 5000;

    /** A single broadcast addressed to more explicit ids than this is a mistake. */
    private const MAX_RECIPIENTS = 5000;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('title', max: self::TITLE_MAX),
            ...$this->translatableRules('body', max: self::BODY_MAX),
            'target_type' => ['required', 'string', 'in:'.implode(',', RecipientResolverService::TYPES)],
            'target_ids' => ['required_unless:target_type,all', 'nullable', 'array', 'max:'.self::MAX_RECIPIENTS],
            'target_ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => __('communication::communication.fields.title'),
            'body' => __('communication::communication.fields.body'),
            'target_type' => __('communication::communication.fields.target_type'),
            'target_ids' => __('communication::communication.fields.target_ids'),
        ];
    }
}
