<?php

namespace Modules\Timetable\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Timetable\Actions\UpdateWeekdaySettingsAction;
use Modules\Timetable\DTOs\UpdateWeekdaySettingsDTO;
use Modules\Timetable\Http\Requests\UpdateWeekdaySettingsRequest;
use Modules\Timetable\Models\Period;

class WeekdaySettingController extends BaseInertiaController
{
    public function update(UpdateWeekdaySettingsRequest $request, UpdateWeekdaySettingsAction $action): RedirectResponse
    {
        $this->authorize('update', Period::class);

        $action->execute(UpdateWeekdaySettingsDTO::fromRequest($request));

        return $this->toRoute('periods.index', [], __('timetable::timetable.updated'));
    }
}
