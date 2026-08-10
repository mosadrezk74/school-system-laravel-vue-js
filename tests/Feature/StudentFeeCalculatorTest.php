<?php

use Modules\Finance\Services\StudentFeeCalculator;
use Modules\Student\Models\StudentProfile;

/**
 * The money rules that must hold whatever a form submits. These run against
 * unsaved models so the arithmetic is tested on its own, without a database.
 */
function studentWithScholarship(float|int|null $percentage): StudentProfile
{
    /*
     * setRawAttributes bypasses the cast, which would otherwise need the
     * column to exist on the connection. The arithmetic under test is pure, so
     * the fixture should not depend on the schema.
     */
    $student = new StudentProfile;
    $student->setRawAttributes(['scholarship_percentage' => $percentage]);

    return $student;
}

it('applies the scholarship percentage recorded on the student', function () {
    $result = (new StudentFeeCalculator)->calculate(studentWithScholarship(50), 10000);

    expect($result['amount'])->toBe(10000.0)
        ->and($result['discount_amount'])->toBe(5000.0)
        ->and($result['final_amount'])->toBe(5000.0);
});

it('preserves the original amount so financial history stays recoverable', function () {
    $result = (new StudentFeeCalculator)->calculate(studentWithScholarship(25), 10000);

    expect($result['amount'])->toBe(10000.0)
        ->and($result['discount_amount'])->toBe(2500.0)
        ->and($result['final_amount'])->toBe(7500.0);
});

it('charges the full amount when the student has no scholarship', function () {
    foreach ([0, null] as $percentage) {
        $result = (new StudentFeeCalculator)->calculate(studentWithScholarship($percentage), 10000);

        expect($result['discount_amount'])->toBe(0.0)
            ->and($result['final_amount'])->toBe(10000.0);
    }
});

it('waives the whole fee at a full scholarship', function () {
    $result = (new StudentFeeCalculator)->calculate(studentWithScholarship(100), 10000);

    expect($result['final_amount'])->toBe(0.0)
        ->and($result['discount_amount'])->toBe(10000.0);
});

it('never produces a negative final amount', function () {
    $result = (new StudentFeeCalculator)->calculate(studentWithScholarship(150), 10000);

    expect($result['final_amount'])->toBe(0.0)
        ->and($result['discount_amount'])->toBe(10000.0);
});

it('ignores a negative scholarship rather than inflating the bill', function () {
    $result = (new StudentFeeCalculator)->calculate(studentWithScholarship(-30), 10000);

    expect($result['discount_amount'])->toBe(0.0)
        ->and($result['final_amount'])->toBe(10000.0);
});

it('keeps discount and final amount adding back up to the original', function () {
    // 33% of 999.99 does not divide cleanly; the parts must still reconcile.
    $result = (new StudentFeeCalculator)->calculate(studentWithScholarship(33), 999.99);

    expect(round($result['discount_amount'] + $result['final_amount'], 2))
        ->toBe(round($result['amount'], 2));
});
