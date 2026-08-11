<?php

namespace Modules\Academic\Services;

use Modules\Academic\Exceptions\AcademicYearNotSetException;
use Modules\Academic\Exceptions\SemesterNotSetException;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;

/**
 * The one place the app asks "which year and term are we in right now?".
 *
 * Resolution is memoised per request in statics, matching AcademicYearService.
 * Tests that mutate the underlying rows must call flush(); the observers do it
 * automatically for writes that go through Eloquent.
 */
class AcademicContextService
{
    private static ?Semester $resolvedSemester = null;

    private static bool $semesterResolved = false;

    public function __construct(
        private readonly AcademicYearService $academicYears,
    ) {}

    public function currentYear(): ?AcademicYear
    {
        return $this->academicYears->current();
    }

    public function currentYearId(): ?int
    {
        return $this->academicYears->currentId();
    }

    public function requireCurrentYearId(): int
    {
        return $this->academicYears->requireCurrentId();
    }

    /**
     * Falls back to the earliest active semester of the current year whose
     * date range covers today, then to the year's first active semester —
     * so a school that never flags a term still gets a sensible answer.
     */
    public function currentSemester(): ?Semester
    {
        if (self::$semesterResolved) {
            return self::$resolvedSemester;
        }

        self::$semesterResolved = true;

        $academicYearId = $this->currentYearId();

        if ($academicYearId === null) {
            return self::$resolvedSemester = null;
        }

        return self::$resolvedSemester = Semester::query()
            ->where('academic_year_id', $academicYearId)
            ->current()
            ->active()
            ->first()
            ?? Semester::query()
                ->where('academic_year_id', $academicYearId)
                ->active()
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->orderBy('sequence')
                ->first()
            ?? Semester::query()
                ->where('academic_year_id', $academicYearId)
                ->active()
                ->orderBy('sequence')
                ->first();
    }

    public function currentSemesterId(): ?int
    {
        return $this->currentSemester()?->id;
    }

    public function requireCurrentSemesterId(): int
    {
        $semester = $this->currentSemester();

        if (! $semester instanceof Semester) {
            throw SemesterNotSetException::missing();
        }

        return $semester->id;
    }

    /**
     * Both IDs at once, for callers that stamp a new record with its academic
     * context. Throws if either side is unset.
     *
     * @return array{academic_year_id: int, semester_id: int}
     *
     * @throws AcademicYearNotSetException
     * @throws SemesterNotSetException
     */
    public function requireContext(): array
    {
        return [
            'academic_year_id' => $this->requireCurrentYearId(),
            'semester_id' => $this->requireCurrentSemesterId(),
        ];
    }

    /**
     * @return array{academic_year_id: int|null, semester_id: int|null}
     */
    public function context(): array
    {
        return [
            'academic_year_id' => $this->currentYearId(),
            'semester_id' => $this->currentSemesterId(),
        ];
    }

    public static function flush(): void
    {
        self::$resolvedSemester = null;
        self::$semesterResolved = false;

        AcademicYearService::flush();
    }

    public static function yearId(): ?int
    {
        return app(self::class)->currentYearId();
    }

    public static function requiredYearId(): int
    {
        return app(self::class)->requireCurrentYearId();
    }

    public static function semesterId(): ?int
    {
        return app(self::class)->currentSemesterId();
    }

    public static function requiredSemesterId(): int
    {
        return app(self::class)->requireCurrentSemesterId();
    }
}
