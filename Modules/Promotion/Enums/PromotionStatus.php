<?php

namespace Modules\Promotion\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * The decision recorded for a student at the end of an academic year.
 *
 * Only Promoted moves a student into a new class and year. The other three are
 * decisions that still belong in the history — a school needs to be able to
 * show why a student did not advance, not just that they did not.
 */
enum PromotionStatus: string
{
    use BaseEnum;

    /** Advances to the target year and class. */
    case Promoted = 'promoted';

    /** Repeats the year: academic state is deliberately left untouched. */
    case Retained = 'retained';

    /** Leaves the school having finished; no next class is assigned. */
    case Graduated = 'graduated';

    /** Leaves for another school. No external-school data is modelled. */
    case Transferred = 'transferred';

    public function label(): string
    {
        return __("promotion::promotions.statuses.{$this->value}");
    }

    public function color(): string
    {
        return match ($this) {
            self::Promoted => 'success',
            self::Retained => 'warning',
            self::Graduated => 'primary',
            self::Transferred => 'secondary',
        };
    }

    /** Only a promotion moves the student, so only it needs a target class. */
    public function requiresTargetClass(): bool
    {
        return $this === self::Promoted;
    }

    /** Whether the decision changes where the student sits next year. */
    public function movesStudent(): bool
    {
        return $this === self::Promoted;
    }

    /**
     * Retained students stay put, so their record carries no target year
     * either — the history row states the decision, nothing more.
     */
    public function requiresTargetYear(): bool
    {
        return $this === self::Promoted;
    }
}
