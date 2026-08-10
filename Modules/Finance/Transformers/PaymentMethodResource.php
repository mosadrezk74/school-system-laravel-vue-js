<?php

namespace Modules\Finance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Finance\Models\PaymentMethod;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'payment_url' => $this->payment_url,
            'bank_name' => $this->bank_name,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'iban' => $this->iban,
            'is_active' => (bool) $this->is_active,
            'display_order' => (int) $this->display_order,
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
