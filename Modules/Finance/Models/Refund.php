<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Abstracts\BaseModel;
use Modules\Finance\Enums\PaymentMethod as PaymentMethodEnum;
use Modules\Student\Models\StudentProfile;

/**
 * A record of money the school decided to return. The transfer itself happens
 * outside the application, so this is an audit entry rather than an
 * instruction to move funds.
 */
class Refund extends BaseModel
{
    protected $table = 'refunds';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refund_date' => 'date',
            'refund_method' => PaymentMethodEnum::class,
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
