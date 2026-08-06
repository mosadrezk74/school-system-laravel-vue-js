<?php

namespace Modules\Academic\Actions;

use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Services\AcademicYearService;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Exceptions\BaseException;

/**
 * Promotes one year to "current" and demotes every other, as a single
 * transaction. Nothing else in the app may set is_current directly — the
 * invariant is that exactly one row holds it.
 */
class SetCurrentAcademicYearAction extends BaseAction
{
    /**
     * @param  AcademicYear  $input
     */
    public function execute(mixed $input): AcademicYear
    {
        return $this->executeWithTransaction(function () use ($input): AcademicYear {
            if (! $input->is_active) {
                throw BaseException::badRequest(
                    __('academic::academic_years.cannot_activate_inactive'),
                );
            }

            AcademicYear::query()
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $input->update([
                'is_current' => true,
                'updated_by' => auth()->id(),
            ]);

            AcademicYearService::flush();

            return $input->refresh();
        });
    }
}
