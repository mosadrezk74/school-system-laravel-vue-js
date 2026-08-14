<?php

namespace Modules\Communication\Http\Requests;

use Modules\Communication\Rules\MessageAttachmentRule;
use Modules\Core\Abstracts\BaseRequest;

class SendMessageRequest extends BaseRequest
{
    /** `messages.message` is TEXT; this is the domain limit the UI enforces too. */
    public const BODY_MAX = 5000;

    /**
     * `body` is nullable rather than required because a message may be nothing
     * but files. "Neither text nor files" is rejected — but in the Action, via
     * ConversationOperationException::emptyMessage(), because it is a rule
     * about the message rather than about either field.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:'.self::BODY_MAX],

            'attachments' => ['nullable', 'array', 'max:'.MessageAttachmentRule::maxPerMessage()],
            'attachments.*' => ['required', 'file', new MessageAttachmentRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'body' => __('communication::chat.fields.body'),
            'attachments' => __('communication::chat.fields.attachments'),
        ];
    }
}
