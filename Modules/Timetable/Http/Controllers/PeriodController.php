<?php

namespace Modules\Timetable\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Setting\Services\SettingsService;
use Modules\Timetable\Actions\CreatePeriodAction;
use Modules\Timetable\Actions\DeletePeriodAction;
use Modules\Timetable\Actions\RestorePeriodAction;
use Modules\Timetable\Actions\UpdatePeriodAction;
use Modules\Timetable\DTOs\CreatePeriodDTO;
use Modules\Timetable\DTOs\UpdatePeriodDTO;
use Modules\Timetable\Enums\Weekday;
use Modules\Timetable\Http\Requests\StorePeriodRequest;
use Modules\Timetable\Http\Requests\UpdatePeriodRequest;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Queries\PeriodQuery;
use Modules\Timetable\Transformers\PeriodResource;

class PeriodController extends BaseInertiaController
{
    public function __construct(private readonly SettingsService $settings) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Period::class);

        $periods = (new PeriodQuery)->sort('period_number')->get();

        return $this->render('Timetable::Periods/Index', [
            'periods' => PeriodResource::collection($periods),
            'weekdays' => Weekday::options(),
            'activeWeekdays' => $this->settings->value('active_weekdays', []),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Period::class);

        return $this->render('Timetable::Periods/Create');
    }

    public function store(StorePeriodRequest $request, CreatePeriodAction $action): RedirectResponse
    {
        $this->authorize('create', Period::class);

        $action->execute(CreatePeriodDTO::fromRequest($request));

        return $this->toRoute('periods.index', [], __('timetable::timetable.created'));
    }

    public function edit(Period $period): Response
    {
        $this->authorize('update', $period);

        return $this->render('Timetable::Periods/Edit', [
            'period' => new PeriodResource($period),
        ]);
    }

    public function update(UpdatePeriodRequest $request, Period $period, UpdatePeriodAction $action): RedirectResponse
    {
        $this->authorize('update', $period);

        $action->execute(['period' => $period, 'dto' => UpdatePeriodDTO::fromRequest($request)]);

        return $this->toRoute('periods.index', [], __('timetable::timetable.updated'));
    }

    public function destroy(Period $period, DeletePeriodAction $action): RedirectResponse
    {
        $this->authorize('delete', $period);

        $action->execute($period);

        return $this->toRoute('periods.index', [], __('timetable::timetable.archived'));
    }

    public function restore(int $period, RestorePeriodAction $action): RedirectResponse
    {
        $period = Period::withTrashed()->findOrFail($period);

        $this->authorize('restore', $period);

        $action->execute($period);

        return $this->toRoute('periods.index', [], __('timetable::timetable.restored'));
    }
}
