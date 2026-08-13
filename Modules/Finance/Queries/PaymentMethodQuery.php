<?php

namespace Modules\Finance\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Abstracts\BaseQuery;
use Modules\Core\Support\Filters\BooleanFilter;
use Modules\Core\Support\Filters\EnumFilter;
use Modules\Core\Support\Filters\Filter;
use Modules\Core\Support\Filters\TextFilter;
use Modules\Core\Support\Filters\TranslatableFilter;
use Modules\Finance\Enums\PaymentMethodType;
use Modules\Finance\Models\PaymentMethod;

class PaymentMethodQuery extends BaseQuery
{
    /** @var array<int, string> */
    protected array $searchableFields = ['code', 'bank_name'];

    /**
     * `name` and `description` are JSON keyed by locale.
     *
     * @var array<int, string>
     */
    protected array $translatableSearchFields = ['name', 'description'];

    /** @var array<int, string> */
    protected array $sortableFields = ['code', 'type', 'is_active', 'display_order', 'created_at'];

    protected string $defaultSortField = 'display_order';

    protected function baseQuery(): Builder
    {
        return PaymentMethod::query();
    }

    /**
     * Account numbers and IBANs are searchable-adjacent settings data, not
     * filtering axes, and are left out on purpose: a list narrowed by IBAN
     * fragment is a way to probe banking details, not a way to find a method.
     *
     * @return array<string, Filter>
     */
    protected function allowedFilters(): array
    {
        return [
            'type' => EnumFilter::make(PaymentMethodType::class)->label('finance.paymentMethods.type'),
            'is_active' => BooleanFilter::make()->label('finance.paymentMethods.active'),
            'name' => TranslatableFilter::make()->label('finance.paymentMethods.name'),
            'code' => TextFilter::make()->label('finance.paymentMethods.code'),
        ];
    }
}
