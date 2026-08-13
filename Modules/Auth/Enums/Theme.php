<?php

namespace Modules\Auth\Enums;

use Modules\Core\Traits\BaseEnum;

/**
 * The palettes the SPA ships. Anything else stored in `users.theme` would
 * leave the layout falling back silently, so it is rejected at the form.
 */
enum Theme: string
{
    use BaseEnum;

    case Light = 'light';
    case Dark = 'dark';

    public function label(): string
    {
        return __("auth::auth.themes.{$this->value}");
    }
}
