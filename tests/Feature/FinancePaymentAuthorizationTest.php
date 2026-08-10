<?php

use App\Models\User;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Enums\PaymentMethodType;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\PaymentMethod;
use Modules\Finance\Models\Refund;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Models\GuardianStudent;
use Modules\Student\Models\StudentProfile;
use Spatie\Permission\PermissionRegistrar;

/**
 * Authorization at the HTTP boundary. These hit real routes rather than the
 * actions directly, because the guarantee that matters is what a browser can
 * reach, not what a unit test can call.
 */
beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    /*
     * RefreshDatabase rolls the tables back between tests but the registrar
     * keeps its resolved permission map in memory, so without this a later
     * test authorises against ids that no longer exist.
     */
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function makeUser(UserRole $role): User
{
    $user = User::factory()->create(['role' => $role->value]);
    $user->assignRole($role->value);

    /*
     * A role assigned after the registrar has already answered a check in this
     * same request is invisible to it until the map is dropped. Tests that act
     * as a parent first and an admin second depend on this.
     */
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user->refresh();
}

function makeGuardianWithChild(): array
{
    $user = makeUser(UserRole::Guardian);
    $guardian = GuardianProfile::query()->create(['user_id' => $user->id]);
    $student = StudentProfile::factory()->create();

    GuardianStudent::query()->create([
        'parent_id' => $guardian->id,
        'student_id' => $student->id,
        'relationship' => 'father',
    ]);

    return [$user, $student];
}

function makeInvoice(StudentProfile $student, float $total = 5000): Invoice
{
    return Invoice::query()->create([
        'student_id' => $student->id,
        'invoice_number' => 'INV-'.fake()->unique()->numerify('#####'),
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addMonth()->toDateString(),
        'subtotal' => $total,
        'discount_amount' => 0,
        'total_amount' => $total,
        'paid_amount' => 0,
        'balance_due' => $total,
        'status' => InvoiceStatus::Pending->value,
        'currency' => 'SAR',
    ]);
}

function makePaymentMethod(bool $isActive = true): PaymentMethod
{
    return PaymentMethod::query()->create([
        'code' => fake()->unique()->lexify('pm-????'),
        'name' => ['en' => 'Test method'],
        'type' => PaymentMethodType::Cash->value,
        'is_active' => $isActive,
        'display_order' => 0,
    ]);
}

it('lets a parent open a payment request for their own child', function () {
    [$parent, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student);
    $method = makePaymentMethod();

    $this->actingAs($parent)
        ->post('/parent/payment-requests', [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $method->id,
        ])
        ->assertRedirect();

    $payment = Payment::query()->where('invoice_id', $invoice->id)->firstOrFail();

    expect($payment->status)->toBe(PaymentStatus::Pending);
});

it('blocks a parent from paying another family invoice', function () {
    [$parentA] = makeGuardianWithChild();
    [, $childB] = makeGuardianWithChild();

    $foreignInvoice = makeInvoice($childB);
    $method = makePaymentMethod();

    $this->actingAs($parentA)
        ->post('/parent/payment-requests', [
            'invoice_id' => $foreignInvoice->id,
            'payment_method_id' => $method->id,
        ])
        ->assertNotFound();

    expect(Payment::query()->where('invoice_id', $foreignInvoice->id)->exists())->toBeFalse();
});

it('ignores an amount supplied by the parent and bills the invoice balance', function () {
    [$parent, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student, 5000);
    $method = makePaymentMethod();

    $this->actingAs($parent)->post('/parent/payment-requests', [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $method->id,
        'amount' => 1,
        'final_amount' => 1,
    ]);

    $payment = Payment::query()->where('invoice_id', $invoice->id)->firstOrFail();

    expect((float) $payment->amount)->toBe(5000.0);
});

it('rejects an inactive payment method', function () {
    [$parent, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student);
    $inactive = makePaymentMethod(isActive: false);

    $this->actingAs($parent)
        ->post('/parent/payment-requests', [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $inactive->id,
        ]);

    expect(Payment::query()->where('invoice_id', $invoice->id)->exists())->toBeFalse();
});

it('does not reduce the invoice balance while a request is pending', function () {
    [$parent, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student, 5000);
    $method = makePaymentMethod();

    $this->actingAs($parent)->post('/parent/payment-requests', [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $method->id,
    ]);

    expect((float) $invoice->refresh()->balance_due)->toBe(5000.0)
        ->and((float) $invoice->paid_amount)->toBe(0.0);
});

it('forbids a parent from confirming their own payment', function () {
    [$parent, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student);
    $method = makePaymentMethod();

    $this->actingAs($parent)->post('/parent/payment-requests', [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $method->id,
    ]);

    $payment = Payment::query()->where('invoice_id', $invoice->id)->firstOrFail();

    $this->actingAs($parent)
        ->patch("/finance/payments/{$payment->id}/confirm")
        ->assertForbidden();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Pending);
});

it('lets an admin confirm a pending payment and settles the invoice', function () {
    [$parent, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student, 5000);
    $method = makePaymentMethod();

    $this->actingAs($parent)->post('/parent/payment-requests', [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $method->id,
    ]);

    $payment = Payment::query()->where('invoice_id', $invoice->id)->firstOrFail();

    $this->actingAs(makeUser(UserRole::Admin))
        ->patch("/finance/payments/{$payment->id}/confirm")
        ->assertRedirect();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Paid)
        ->and((float) $invoice->refresh()->paid_amount)->toBe(5000.0)
        ->and((float) $invoice->balance_due)->toBe(0.0);
});

it('forbids a parent from recording a refund', function () {
    [$parent, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student);

    $payment = Payment::query()->create([
        'invoice_id' => $invoice->id,
        'student_id' => $student->id,
        'amount' => 5000,
        'status' => PaymentStatus::Paid->value,
        'paid_at' => now(),
    ]);

    $this->actingAs($parent)
        ->post('/finance/refunds', [
            'payment_id' => $payment->id,
            'amount' => 100,
            'refund_date' => now()->toDateString(),
        ])
        ->assertForbidden();

    expect(Refund::query()->count())->toBe(0);
});

it('lets an admin record a partial refund and marks it partially refunded', function () {
    [, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student);

    $payment = Payment::query()->create([
        'invoice_id' => $invoice->id,
        'student_id' => $student->id,
        'amount' => 5000,
        'status' => PaymentStatus::Paid->value,
        'paid_at' => now(),
    ]);

    $this->actingAs(makeUser(UserRole::Admin))
        ->post('/finance/refunds', [
            'payment_id' => $payment->id,
            'amount' => 2000,
            'refund_date' => now()->toDateString(),
        ])
        ->assertRedirect();

    expect($payment->refresh()->status)->toBe(PaymentStatus::PartiallyRefunded)
        ->and((float) $payment->refunded_amount)->toBe(2000.0);
});

it('refuses a refund larger than the amount actually paid', function () {
    [, $student] = makeGuardianWithChild();
    $invoice = makeInvoice($student);

    $payment = Payment::query()->create([
        'invoice_id' => $invoice->id,
        'student_id' => $student->id,
        'amount' => 5000,
        'status' => PaymentStatus::Paid->value,
        'paid_at' => now(),
    ]);

    $this->actingAs(makeUser(UserRole::Admin))
        ->post('/finance/refunds', [
            'payment_id' => $payment->id,
            'amount' => 6000,
            'refund_date' => now()->toDateString(),
        ]);

    expect(Refund::query()->count())->toBe(0)
        ->and($payment->refresh()->status)->toBe(PaymentStatus::Paid);
});

it('keeps parents out of payment method management', function () {
    [$parent] = makeGuardianWithChild();

    $this->actingAs($parent)->get('/finance/payment-methods')->assertForbidden();

    $this->actingAs($parent)
        ->post('/finance/payment-methods', [
            'code' => 'sneaky',
            'name' => ['en' => 'Sneaky'],
            'type' => PaymentMethodType::Cash->value,
        ])
        ->assertForbidden();

    expect(PaymentMethod::query()->where('code', 'sneaky')->exists())->toBeFalse();
});

it('lets an admin manage payment methods', function () {
    $this->actingAs(makeUser(UserRole::Admin))
        ->post('/finance/payment-methods', [
            'code' => 'instapay',
            'name' => ['en' => 'InstaPay'],
            'type' => PaymentMethodType::Online->value,
            'payment_url' => 'https://example.test/pay',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(PaymentMethod::query()->where('code', 'instapay')->exists())->toBeTrue();
});
