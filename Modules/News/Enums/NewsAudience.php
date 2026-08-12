<?php

namespace Modules\News\Enums;

use Modules\Auth\Enums\UserRole;
use Modules\Core\Traits\BaseEnum;

/**
 * Who a news item is written for.
 *
 * A single audience per item rather than the multi-target shape
 * broadcast_messages uses: news is a school notice board, not a dispatch with
 * a recipient list, so "everyone" or "one group" is the whole requirement.
 */
enum NewsAudience: string
{
    use BaseEnum;

    case All = 'all';
    case Students = 'students';
    case Guardians = 'guardians';
    case Teachers = 'teachers';

    public function label(): string
    {
        return __("news::news.audiences.{$this->value}");
    }

    /**
     * The audiences a given account type may read.
     *
     * Everyone sees All; each role additionally sees its own. Staff are
     * handled by the caller, which does not filter for them at all.
     *
     * @return array<int, string>
     */
    public static function visibleTo(?UserRole $role): array
    {
        $own = match ($role) {
            UserRole::Student => self::Students,
            UserRole::Guardian => self::Guardians,
            UserRole::Teacher => self::Teachers,
            default => null,
        };

        return $own === null
            ? [self::All->value]
            : [self::All->value, $own->value];
    }
}
