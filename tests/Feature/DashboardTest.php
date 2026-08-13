<?php

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Services\AcademicContextService;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Classroom\Models\Classroom;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Enums\PaymentStatus;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\Payment;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Modules\Teacher\Models\TeacherProfile;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->year = AcademicYear::factory()->current()->create();

    // The current year is memoised per process; tests create their own.
    AcademicContextService::flush();

    $this->admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $this->admin->assignRole('admin');
});

/** Enrols an active student in the given year. */
function dashboardStudent(AcademicYear $year, array $overrides = []): StudentProfile
{
    return StudentProfile::factory()->create(array_merge([
        'academic_year_id' => $year->id,
        'is_active' => true,
        'is_graduated' => false,
    ], $overrides));
}

function dashboardClass(AcademicYear $year, array $overrides = []): SchoolClass
{
    return SchoolClass::create(array_merge([
        'name' => fake()->unique()->bothify('Class-##'),
        'academic_year_id' => $year->id,
        'grade_level_id' => GradeLevel::create([
            'name' => fake()->unique()->bothify('Grade-##'),
            'level' => fake()->unique()->numberBetween(1, 900),
            'is_active' => true,
        ])->id,
        'capacity' => 30,
        'is_active' => true,
    ], $overrides));
}

function dashboardInvoice(StudentProfile $student, array $overrides = []): Invoice
{
    return Invoice::create(array_merge([
        'student_id' => $student->id,
        'invoice_number' => fake()->unique()->bothify('INV-####'),
        'issue_date' => now()->subDays(10),
        'due_date' => now()->addDays(10),
        'subtotal' => 1000,
        'total_amount' => 1000,
        'paid_amount' => 0,
        'balance_due' => 1000,
        'status' => InvoiceStatus::Pending->value,
    ], $overrides));
}

function dashboardOverview(User $user): array
{
    return test()->actingAs($user)
        ->get(route('dashboard'))
        ->viewData('page')['props']['overview'];
}

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
});

test('the current academic year comes from the flagged year', function () {
    AcademicYear::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('academicYear.id', $this->year->id)
            ->where('academicYear.name', $this->year->name));
});

test('headcounts report only active records for the current year', function () {
    $class = dashboardClass($this->year);

    dashboardStudent($this->year, ['current_class_id' => $class->id]);
    dashboardStudent($this->year, ['current_class_id' => $class->id]);

    // None of these should be counted.
    dashboardStudent($this->year, ['is_active' => false]);
    dashboardStudent($this->year, ['is_graduated' => true]);
    dashboardStudent(AcademicYear::factory()->create(['is_current' => false]));

    $teacher = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);
    TeacherProfile::create([
        'user_id' => $teacher->id,
        'employee_id' => 'EMP-1',
        'joining_date' => now()->toDateString(),
        'is_active' => true,
    ]);

    $overview = dashboardOverview($this->admin);

    expect($overview['people']['students'])->toBe(2)
        ->and($overview['people']['teachers'])->toBe(1)
        ->and($overview['people']['classes'])->toBe(1);
});

test('guardian and classroom headcounts report only active records', function () {
    $guardian = User::factory()->create(['role' => UserRole::Guardian, 'is_active' => true]);
    GuardianProfile::create(['user_id' => $guardian->id, 'is_active' => true]);

    // An inactive profile and a disabled account must both be excluded.
    $inactive = User::factory()->create(['role' => UserRole::Guardian, 'is_active' => true]);
    GuardianProfile::create(['user_id' => $inactive->id, 'is_active' => false]);

    $disabled = User::factory()->create(['role' => UserRole::Guardian, 'is_active' => false]);
    GuardianProfile::create(['user_id' => $disabled->id, 'is_active' => true]);

    Classroom::create(['name' => 'Room A', 'code' => 'A-1', 'capacity' => 30, 'is_active' => true]);
    Classroom::create(['name' => 'Room B', 'code' => 'B-1', 'capacity' => 30, 'is_active' => false]);

    $people = dashboardOverview($this->admin)['people'];

    expect($people['guardians'])->toBe(1)
        ->and($people['classrooms'])->toBe(1);
});

test('a student whose user account is deactivated is not counted', function () {
    $student = dashboardStudent($this->year);
    $student->user->update(['is_active' => false]);

    expect(dashboardOverview($this->admin)['people']['students'])->toBe(0);
});

test('outstanding fees sum the unpaid balance of collectable invoices', function () {
    $student = dashboardStudent($this->year);

    Invoice::create([
        'student_id' => $student->id,
        'invoice_number' => 'INV-1',
        'issue_date' => now()->subDays(10),
        'due_date' => now()->addDays(10),
        'subtotal' => 1000,
        'total_amount' => 1000,
        'paid_amount' => 400,
        'balance_due' => 600,
        'status' => InvoiceStatus::Partial->value,
    ]);

    // Cancelled invoices are not collectable and must be excluded.
    Invoice::create([
        'student_id' => $student->id,
        'invoice_number' => 'INV-2',
        'issue_date' => now()->subDays(10),
        'due_date' => now()->addDays(10),
        'subtotal' => 500,
        'total_amount' => 500,
        'paid_amount' => 0,
        'balance_due' => 500,
        'status' => InvoiceStatus::Cancelled->value,
    ]);

    $finance = dashboardOverview($this->admin)['finance'];

    expect($finance['hasInvoices'])->toBeTrue()
        ->and($finance['outstanding']['amount'])->toBe(600.0)
        ->and($finance['outstanding']['invoices'])->toBe(1);
});

test('a pending payment request is never counted as collected', function () {
    $student = dashboardStudent($this->year);
    $invoice = dashboardInvoice($student);

    Payment::create([
        'invoice_id' => $invoice->id,
        'student_id' => $student->id,
        'amount' => 300,
        'status' => PaymentStatus::Pending->value,
        'paid_at' => now(),
        'payment_method' => 'cash',
    ]);

    $finance = dashboardOverview($this->admin)['finance'];

    expect($finance['collectedThisMonth'])->toBe(0.0)
        ->and($finance['awaitingConfirmation']['count'])->toBe(1)
        ->and($finance['awaitingConfirmation']['amount'])->toBe(300.0);
});

test('collected revenue is reported net of refunds', function () {
    $student = dashboardStudent($this->year);
    $invoice = dashboardInvoice($student);

    Payment::create([
        'invoice_id' => $invoice->id,
        'student_id' => $student->id,
        'amount' => 1000,
        'refunded_amount' => 250,
        'status' => PaymentStatus::PartiallyRefunded->value,
        'paid_at' => now(),
        'payment_method' => 'cash',
    ]);

    // Failed money never reached the school.
    Payment::create([
        'invoice_id' => $invoice->id,
        'student_id' => $student->id,
        'amount' => 900,
        'status' => PaymentStatus::Failed->value,
        'paid_at' => now(),
        'payment_method' => 'cash',
    ]);

    expect(dashboardOverview($this->admin)['finance']['collectedThisMonth'])->toBe(750.0);
});

test('overdue invoices are surfaced as an attention item', function () {
    $student = dashboardStudent($this->year);

    Invoice::create([
        'student_id' => $student->id,
        'invoice_number' => 'INV-3',
        'issue_date' => now()->subDays(40),
        'due_date' => now()->subDays(5),
        'subtotal' => 800,
        'total_amount' => 800,
        'paid_amount' => 0,
        'balance_due' => 800,
        'status' => InvoiceStatus::Pending->value,
    ]);

    $overview = dashboardOverview($this->admin);

    expect($overview['finance']['overdue']['invoices'])->toBe(1)
        ->and(collect($overview['attention'])->pluck('key'))->toContain('overdueInvoices');
});

test('attendance reports not-taken rather than zero when nothing is recorded', function () {
    dashboardStudent($this->year);

    $today = dashboardOverview($this->admin)['attendance']['today'];

    expect($today['taken'])->toBeFalse()
        ->and($today['rate'])->toBeNull();
});

test('the dashboard renders with an empty database', function () {
    $overview = dashboardOverview($this->admin);

    expect($overview['people']['students'])->toBe(0)
        ->and($overview['finance']['hasInvoices'])->toBeFalse()
        ->and($overview['attendance']['today']['taken'])->toBeFalse()
        ->and($overview['attention'])->toBe([]);
});

test('portal users never reach the admin dashboard', function () {
    $teacher = User::factory()->create(['role' => UserRole::Teacher, 'is_active' => true]);
    $teacher->assignRole('teacher');

    $this->actingAs($teacher)->get(route('dashboard'))->assertRedirect();
});

test('finance figures are withheld from an admin without finance permission', function () {
    $student = dashboardStudent($this->year);
    dashboardInvoice($student);

    $this->admin->revokePermissionTo('invoice.view');
    $this->admin->removeRole('admin');

    $overview = dashboardOverview($this->admin);

    expect($overview['finance'])->toBeNull()
        // The attention panel must not leak the figures either.
        ->and(collect($overview['attention'])->pluck('key'))->not->toContain('overdueInvoices');
});

test('quick actions only include actions the user may perform', function () {
    $this->admin->removeRole('admin');
    $this->admin->givePermissionTo('student.create');

    expect(collect(dashboardOverview($this->admin)['quickActions'])->pluck('key'))
        ->toContain('addStudent')
        ->not->toContain('createInvoice');
});
