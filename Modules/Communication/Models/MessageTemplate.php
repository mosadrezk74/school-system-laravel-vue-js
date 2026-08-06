<?php

namespace Modules\Communication\Models;

use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;

class MessageTemplate extends BaseModel
{
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['subject', 'body'];

    protected $table = 'message_templates';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
