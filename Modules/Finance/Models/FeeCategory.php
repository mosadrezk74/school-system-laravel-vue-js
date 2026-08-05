<?php

namespace Modules\Finance\Models;

use Modules\Core\Abstracts\BaseModel;
use Spatie\Translatable\HasTranslations;

class FeeCategory extends BaseModel
{
    /*
     * Spatie stores these as JSON keyed by locale, so one row serves every
     * language the school has enabled. It also casts them itself — listing
     * them as 'array' too would return raw arrays instead of the translation.
     */
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name', 'description'];

    protected $table = 'fee_categories';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
