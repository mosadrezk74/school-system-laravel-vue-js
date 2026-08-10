<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Abstracts\BaseModel;
use Modules\Finance\Enums\PaymentMethod as PaymentMethodEnum;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Student\Models\StudentProfile;

class Payment extends BaseModel
{
    protected $table = 'payments';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'payment_method' => PaymentMethodEnum::class,
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }

    /**
     * Payments whose money has actually reached the school. Invoice totals are
     * built from this scope so a pending parent request never counts as paid.
     */
    public function scopeSettled(Builder $query): Builder
    {
        return $query->whereIn('status', PaymentStatus::settledValues());
    }

    /**
     * What may still be refunded, after the refunds already recorded. Callers
     * that write must read this under a row lock.
     */
    public function refundableAmount(): float
    {
        return max(0, round((float) $this->amount - (float) $this->refunded_amount, 2));
    }
}
