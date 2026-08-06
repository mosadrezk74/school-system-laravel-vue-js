<?php

namespace Modules\Communication\Http\Requests;

use Modules\Communication\Services\RecipientResolverService;
use Modules\Core\Abstracts\BaseRequest;

class StoreMessageRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->translatableRules('title'),
            ...$this->translatableRules('body'),
            'target_type' => ['required', 'string', 'in:'.implode(',', RecipientResolverService::TYPES)],
            'target_ids' => ['required_unless:target_type,all', 'nullable', 'array'],
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
