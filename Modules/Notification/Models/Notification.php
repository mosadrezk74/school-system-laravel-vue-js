<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $guarded = [];

    /**
     * Laravel writes this table with a UUID primary key, so the model must not
     * treat the key as an auto-incrementing integer.
     */
    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }
}
