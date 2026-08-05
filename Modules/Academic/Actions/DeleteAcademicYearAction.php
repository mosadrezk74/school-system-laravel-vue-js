<?php

namespace Modules\Academic\Actions;

use Modules\Academic\Models\AcademicYear;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Exceptions\BaseException;

class DeleteAcademicYearAction extends BaseAction
{
    /**
     * @param  AcademicYear  $input
     */
    public function execute(mixed $input): bool
    {
        return $this->executeWithTransaction(function () use ($input): bool {
            /*
             * The current year anchors enrolments, timetables and invoices.
             * Deleting it would leave those records pointing at a year the
             * school no longer considers active, so require an explicit
             * switch to another year first.
             */
            if ($input->is_current) {
                throw BaseException::badRequest(
                    __('academic::academic_years.cannot_delete_current'),
                );
            }

            return (bool) $input->delete();
        });
    }
}
