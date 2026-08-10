<?php

namespace Modules\Finance\Services;

use Modules\Student\Models\StudentProfile;

/**
 * Works out what a student actually owes for a fee, applying the scholarship
 * recorded on their profile.
 *
 * This is the only place the sum is done. Amounts arriving from a form are
 * treated as a request, never as an answer: the caller passes the gross amount
 * and gets back the breakdown, so a tampered payload cannot lower a bill.
 */
class StudentFeeCalculator
{
    /**
     * @return array{amount: float, discount_amount: float, final_amount: float, scholarship_percentage: float}
     */
    public function calculate(StudentProfile $student, float $amount): array
    {
        $gross = (float) max(0, round($amount, 2));
        $percentage = $this->scholarshipPercentageFor($student);

        /*
         * Rounded before subtracting so the stored discount and the stored
         * final amount always add back up to the original to the cent, rather
         * than drifting apart by a half-cent on odd percentages.
         */
        $discount = (float) min(round($gross * $percentage / 100, 2), $gross);

        return [
            'amount' => $gross,
            'discount_amount' => $discount,
            'final_amount' => (float) max(0, round($gross - $discount, 2)),
            'scholarship_percentage' => $percentage,
        ];
    }

    /**
     * Clamped to 0–100: a negative percentage would inflate a bill above its
     * own price and anything over 100 would produce a negative balance.
     */
    public function scholarshipPercentageFor(StudentProfile $student): float
    {
        return min(100, max(0, round((float) $student->scholarship_percentage, 2)));
    }
}
