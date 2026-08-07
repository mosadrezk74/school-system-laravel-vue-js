<?php

namespace Modules\Finance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Finance\Models\InvoiceItem;

/**
 * @mixin InvoiceItem
 */
class InvoiceItemResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'fee_structure_id' => $this->fee_structure_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'total' => $this->total,

            'fee_structure' => $this->whenLoaded('feeStructure', fn () => $this->feeStructure ? [
                'id' => $this->feeStructure->id,
                'name' => $this->feeStructure->name,
            ] : null),
        ];
    }
}
