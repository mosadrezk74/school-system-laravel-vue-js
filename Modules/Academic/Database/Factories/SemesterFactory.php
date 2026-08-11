<?php

namespace Modules\Academic\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    protected $model = Semester::class;

    /** Backs the per-year unique `sequence`; see definition(). */
    protected static int $sequence = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /*
         * sequence is unique per academic year, so it is drawn from a counter
         * rather than randomly to keep generated rows from colliding.
         */
        $sequence = (static::$sequence++ % 100) + 1;

        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => ['en' => 'Term '.$sequence, 'ar' => 'الفصل '.$sequence],
            'sequence' => $sequence,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'exam_start_date' => null,
            'exam_end_date' => null,
            'is_current' => false,
            'is_active' => true,
        ];
    }

    /** The single term the school is currently teaching. */
    public function current(): static
    {
        return $this->state(fn (): array => [
            'is_current' => true,
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
