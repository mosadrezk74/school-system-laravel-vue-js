<?php

namespace Modules\Communication\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Communication\Enums\ConversationType;
use Modules\Core\Abstracts\BaseRequest;

class StoreConversationRequest extends BaseRequest
{
    /** `conversations.name` is varchar(255); this is the domain limit. */
    public const NAME_MAX = 120;

    /** Matches SendMessageRequest, so the opening message obeys one limit. */
    public const BODY_MAX = 5000;

    /**
     * A thread larger than this is a broadcast, and the module already has a
     * screen for that.
     */
    public const MAX_PARTICIPANTS = 50;

    /**
     * Participant ids are only checked for *shape* here. Whether the signed-in
     * user may actually message them is a domain question answered by
     * ConversationEligibilityService inside the Action — `exists` would let
     * any valid user id through.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ConversationType::class)],

            'name' => [
                Rule::requiredIf(fn (): bool => $this->input('type') === ConversationType::Group->value),
                'nullable',
                'string',
                'max:'.self::NAME_MAX,
            ],

            'participant_ids' => ['required', 'array', 'min:1', 'max:'.self::MAX_PARTICIPANTS],
            'participant_ids.*' => ['required', 'integer', 'distinct', 'min:1'],

            'body' => ['nullable', 'string', 'max:'.self::BODY_MAX],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => __('communication::chat.fields.type'),
            'name' => __('communication::chat.fields.name'),
            'participant_ids' => __('communication::chat.fields.participants'),
            'body' => __('communication::chat.fields.body'),
        ];
    }
}
