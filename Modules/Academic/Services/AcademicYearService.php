<?php

namespace Modules\Academic\Services;

use Modules\Academic\Exceptions\AcademicYearNotSetException;
use Modules\Academic\Models\AcademicYear;

class AcademicYearService
{
    private static ?AcademicYear $resolved = null;

    public function current(): ?AcademicYear
    {
        if (self::$resolved instanceof AcademicYear) {
            return self::$resolved;
        }

        return self::$resolved = AcademicYear::query()
            ->current()
            ->active()
            ->first()
            ?? AcademicYear::query()
                ->active()
                ->orderByDesc('start_date')
                ->first();
    }

    public function currentId(): ?int
    {
        return $this->current()?->id;
    }

    public function requireCurrentId(): int
    {
        $academicYear = $this->current();

        if (! $academicYear instanceof AcademicYear) {
            throw AcademicYearNotSetException::missing();
        }

        return $academicYear->id;
    }

    public static function flush(): void
    {
        self::$resolved = null;
    }

    public static function id(): ?int
    {
        return app(self::class)->currentId();
    }

    public static function requiredId(): int
    {
        return app(self::class)->requireCurrentId();
    }
}
