<?php

namespace Modules\Setting\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * Lifecycle of one version of the school's terms.
 *
 * Exactly one version may be Published at a time; publishing a new one moves
 * the incumbent to Archived. A Draft is the only editable state.
 */
enum SchoolTermStatus: string
{
    use BaseEnum;

    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return __("setting::school_terms.statuses.{$this->value}");
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Published => 'success',
            self::Archived => 'secondary',
        };
    }

    /** Only a draft may have its wording changed or be deleted. */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /** Only the published version may be accepted by a family. */
    public function isAcceptable(): bool
    {
        return $this === self::Published;
    }
}
