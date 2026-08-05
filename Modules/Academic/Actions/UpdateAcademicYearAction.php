<?php

namespace Modules\Academic\Actions;

use Modules\Academic\DTOs\AcademicYearDTO;
use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseAction;

class UpdateAcademicYearAction extends BaseAction
{
    /**
     * @param  array{year: AcademicYear, dto: AcademicYearDTO}  $input
     */
    public function execute(mixed $input): AcademicYear
    {
        return $this->executeWithTransaction(function () use ($input): AcademicYear {
            /** @var AcademicYear $year */
            $year = $input['year'];
            /** @var AcademicYearDTO $dto */
            $dto = $input['dto'];

            $attributes = $dto->toUpdateArray();
            $attributes['updated_by'] = auth()->id();

            $year->update($attributes);

            if ($year->is_current) {
                AcademicYear::query()
                    ->whereKeyNot($year->getKey())
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            return $year->refresh();
        });
    }
}
