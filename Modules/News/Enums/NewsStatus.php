<?php

namespace Modules\News\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * Whether a news item has been released to the school.
 *
 * Only Published reaches the portal, and only once published_at has passed —
 * the two together are what let an admin write ahead of time and have the item
 * appear on the day it is meant to.
 */
enum NewsStatus: string
{
    use BaseEnum;

    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return __("news::news.statuses.{$this->value}");
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Published => 'success',
        };
    }

    public function isPublished(): bool
    {
        return $this === self::Published;
    }
}
