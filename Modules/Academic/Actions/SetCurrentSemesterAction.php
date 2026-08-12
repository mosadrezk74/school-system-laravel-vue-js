<?php

namespace Modules\Academic\Actions;

use Modules\Academic\Models\Semester;
use Modules\Core\Abstracts\BaseAction;
use Modules\Core\Exceptions\BaseException;

class SetCurrentSemesterAction extends BaseAction
{
    /**
     * @param  Semester  $input
     */
    public function execute(mixed $input): Semester
    {
        return $this->executeWithTransaction(function () use ($input): Semester {
            if (! $input->is_active) {
                throw BaseException::badRequest(
                    __('academic::semesters.cannot_activate_inactive'),
                );
            }

            Semester::query()
                ->where('is_current', true)
                ->where('id', '!=', $input->id)
                ->update(['is_current' => false]);

            $input->update([
                'is_current' => true,
                'updated_by' => auth()->id(),
            ]);

            return $input->refresh();
        });
    }
}
