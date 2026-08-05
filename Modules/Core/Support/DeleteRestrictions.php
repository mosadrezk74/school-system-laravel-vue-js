<?php

namespace Modules\Core\Support;

class DeleteRestrictions
{
    private const KEY = 'core.delete-restrictions-suspended';

    public static function suspended(): bool
    {
        return app()->bound(self::KEY);
    }

    public static function without(callable $callback): mixed
    {
        $already = self::suspended();

        if (! $already) {
            app()->instance(self::KEY, true);
        }

        try {
            return $callback();
        } finally {
            if (! $already) {
                app()->forgetInstance(self::KEY);
            }
        }
    }
}
