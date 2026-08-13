<?php

namespace Modules\Auth\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * The two values `users.gender` has ever stored. Previously spelled as an
 * inline `in:male,female` in five separate requests, which meant the student,
 * teacher, admin and profile forms each had their own copy of the domain.
 */
enum Gender: string
{
    use BaseEnum;

    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return __("auth::auth.genders.{$this->value}");
    }
}
