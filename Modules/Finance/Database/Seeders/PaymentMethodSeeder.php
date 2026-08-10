<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Enums\PaymentMethodType;
use Modules\Finance\Models\PaymentMethod;

/**
 * Starter payment methods so a fresh install has something to configure rather
 * than an empty screen.
 *
 * Every provider-specific value — payment links, bank accounts, IBANs — is
 * deliberately left empty. Real banking details are configuration the school
 * enters itself, never something committed to source control. Rows are matched
 * on `code` so re-seeding will not overwrite what an admin has since filled in.
 */
class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => 'cash',
                'name' => ['en' => 'Cash', 'ar' => 'نقداً'],
                'type' => PaymentMethodType::Cash->value,
                'display_order' => 1,
            ],
            [
                'code' => 'bank_transfer',
                'name' => ['en' => 'Bank transfer', 'ar' => 'تحويل بنكي'],
                'type' => PaymentMethodType::BankTransfer->value,
                'display_order' => 2,
            ],
            [
                'code' => 'instapay',
                'name' => ['en' => 'InstaPay', 'ar' => 'انستا باي'],
                'type' => PaymentMethodType::Online->value,
                'display_order' => 3,
            ],
            [
                'code' => 'mada',
                'name' => ['en' => 'Mada', 'ar' => 'مدى'],
                'type' => PaymentMethodType::Online->value,
                'display_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->firstOrCreate(
                ['code' => $method['code']],
                [
                    ...$method,
                    /*
                     * Online methods start disabled: without a payment URL
                     * they would appear to a parent as a choice that goes
                     * nowhere. The admin enables them once configured.
                     */
                    'is_active' => $method['type'] !== PaymentMethodType::Online->value,
                ],
            );
        }
    }
}
