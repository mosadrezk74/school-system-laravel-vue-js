<?php

namespace Modules\GuardianProfile\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * How an adult is related to a student. Backs the guardian link, the student
 * record's guardian relation and its emergency-contact relation — all three
 * previously took arbitrary text up to 50 characters, so the same person could
 * be filed as "Father", "father", "dad" and "F" across four screens with no
 * way to report on any of it.
 *
 * `Other` is deliberate: the list has to stay short enough to pick from, and a
 * legal arrangement the school does not have a word for still needs a home.
 */
enum RelationshipType: string
{
    use BaseEnum;

    case Father = 'father';
    case Mother = 'mother';
    case Grandfather = 'grandfather';
    case Grandmother = 'grandmother';
    case Brother = 'brother';
    case Sister = 'sister';
    case Uncle = 'uncle';
    case Aunt = 'aunt';
    case LegalGuardian = 'legal_guardian';
    case Other = 'other';

    public function label(): string
    {
        return __("guardian::guardian.relationships.{$this->value}");
    }
}
