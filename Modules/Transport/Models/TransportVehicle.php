<?php

namespace Modules\Transport\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;

class TransportVehicle extends BaseModel
{
    protected $table = 'transport_vehicles';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'insurance_expiry' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
