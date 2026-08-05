<?php

namespace Modules\Academic\Actions;

use Modules\Academic\DTOs\AcademicYearDTO;
use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseAction;

class CreateAcademicYearAction extends BaseAction
{
    /**
     * @param  AcademicYearDTO  $input
     */
    public function execute(mixed $input): AcademicYear
    {
        return $this->executeWithTransaction(function () use ($input): AcademicYear {
            $attributes = $input->toCreateArray();
            $attributes['created_by'] = auth()->id();

            $year = AcademicYear::create($attributes);

            // Creating a year as "current" must still demote the previous one.
            if ($year->is_current) {
                $this->demoteOtherYears($year);
            }

            return $year;
        });
    }

    protected function demoteOtherYears(AcademicYear $year): void
    {
        AcademicYear::query()
            ->whereKeyNot($year->getKey())
            ->where('is_current', true)
            ->update(['is_current' => false]);
    }
}
