<?php

namespace Modules\Auth\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * The coarse account type stored on users.role.
 *
 * This decides which shell a person sees after signing in — a parent never
 * gets the staff dashboard. What they may *do* inside that shell is Spatie
 * permissions, not this enum.
 */
enum UserRole: string
{
    use BaseEnum;

    case Owner = 'owner';
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';
    case Guardian = 'guardian';
    case Staff = 'staff';

    public function label(): string
    {
        return __("auth::roles.{$this->value}");
    }

    /** Roles that belong to the school's payroll rather than its families. */
    public function isEmployee(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::Teacher, self::Staff], true);
    }
}
