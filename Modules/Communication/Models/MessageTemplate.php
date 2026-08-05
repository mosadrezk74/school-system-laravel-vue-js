<?php

namespace Modules\Communication\Models;

use Modules\Core\Abstracts\BaseModel;

class MessageTemplate extends BaseModel
{
    protected $table = 'message_templates';
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subject' => 'array',
            'body' => 'array',
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
