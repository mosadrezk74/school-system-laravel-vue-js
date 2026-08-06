<?php

namespace Modules\Timetable\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Timetable\Actions\UpsertTimetableEntriesAction;
use Modules\Timetable\DTOs\UpsertTimetableEntriesDTO;
use Modules\Timetable\Http\Requests\UpdateTimetableEntriesRequest;
use Modules\Timetable\Models\Timetable;

class TimetableEntryController extends BaseInertiaController
{
    public function update(UpdateTimetableEntriesRequest $request, Timetable $timetable, UpsertTimetableEntriesAction $action): RedirectResponse
    {
        $this->authorize('update', $timetable);

        $action->execute(UpsertTimetableEntriesDTO::fromRequest($request, $timetable->id));

        return $this->toRoute('timetables.edit', ['timetable' => $timetable], __('timetable::timetable.entries_saved'));
    }
}
