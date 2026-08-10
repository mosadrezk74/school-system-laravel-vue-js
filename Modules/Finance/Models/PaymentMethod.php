<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Abstracts\BaseModel;
use Modules\Finance\Enums\PaymentMethodType;
use Spatie\Translatable\HasTranslations;

/**
 * An admin-configured way to pay. Deliberately a table rather than an enum:
 * the school adds and retires providers without a deploy, and the payment URL
 * and bank details are configuration that must never be hardcoded.
 */
class PaymentMethod extends BaseModel
{
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name', 'description', 'instructions'];

    protected $table = 'payment_methods';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_method_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The set a parent is allowed to choose from. Used by both the portal
     * screen and the request validation, so an inactive method cannot be
     * selected by posting its id directly.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }
}
