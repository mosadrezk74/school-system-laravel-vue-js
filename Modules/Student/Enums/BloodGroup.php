<?php

namespace Modules\Student\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * The eight ABO/Rh groups. A whitelist rather than a length cap, because a
 * typo like "A++" reaching a medical record is worse than no value at all.
 */
enum BloodGroup: string
{
    use BaseEnum;

    case APositive = 'A+';
    case ANegative = 'A-';
    case BPositive = 'B+';
    case BNegative = 'B-';
    case ABPositive = 'AB+';
    case ABNegative = 'AB-';
    case OPositive = 'O+';
    case ONegative = 'O-';

    /**
     * "A+" and "O-" read identically in every locale the app ships, so the
     * value is its own label and there is nothing to translate.
     */
    public function label(): string
    {
        return $this->value;
    }
}
