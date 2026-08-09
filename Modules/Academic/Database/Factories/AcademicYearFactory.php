<?php

namespace Modules\Academic\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Academic\Models\AcademicYear;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    /** Counter backing the unique code; see definition(). */
    protected static int $yearSequence = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /*
         * Sequential rather than random: `code` is unique, and deriving it
         * from a random year made two generated rows collide often enough to
         * break test runs.
         */
        $startYear = 2000 + static::$yearSequence++;

        return [
            'name' => \sprintf('%d/%d', $startYear, $startYear + 1),
            'start_date' => \sprintf('%d-09-01', $startYear),
            'end_date' => \sprintf('%d-06-30', $startYear + 1),
            'is_current' => false,
            'is_active' => true,
        ];
    }

    /** The single year the school is currently operating in. */
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
