<?php

namespace Modules\Inventory\Models;

use Modules\Core\Abstracts\BaseModel;

class InventorySupplier extends BaseModel
{
    protected $table = 'inventory_suppliers';

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
