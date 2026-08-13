<?php

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Promotion\Actions\PromoteStudentsAction;
use Modules\Promotion\Actions\RevertPromotionAction;
use Modules\Promotion\DTOs\PromoteStudentsDTO;
use Modules\Promotion\DTOs\PromotionFilterDTO;
use Modules\Promotion\Enums\PromotionStatus;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
use Modules\Promotion\Models\StudentPromotion;
use Modules\Promotion\Queries\PromotionEligibilityQuery;
use Modules\Promotion\Queries\PromotionQuery;
use Modules\Promotion\Transformers\PromotionResource;
use Modules\SchoolClass\Models\SchoolClass;

class PromotionController extends BaseInertiaController
{
    public function __construct(private readonly AcademicContextService $context) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', StudentPromotion::class);

        $filters = PromotionFilterDTO::fromRequest($request);

        $promotions = (new PromotionQuery)
            ->search($filters->search)
            ->status($filters->status)
            ->fromAcademicYear($filters->fromAcademicYearId)
            ->toAcademicYear($filters->toAcademicYearId)
            ->latestFirst()
            ->paginate($filters->perPage);

        return $this->render('Promotion::Promotions/Index', [
            'promotions' => PromotionResource::collection($promotions),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'status', 'from_academic_year_id']),
            'statusOptions' => $this->statusOptions(),
            'academicYears' => $this->academicYearOptions(),
        ]);
    }

    /**
     * The promotion wizard.
     *
     * The source year comes from the academic context; the target year is the
     * admin's choice and arrives as a query parameter so the eligible list and
     * available classes can be reloaded as they pick.
     */
    public function create(Request $request, PromotionEligibilityQuery $eligibility): Response
    {
        $this->authorize('create', StudentPromotion::class);

        ['academic_year_id' => $sourceYearId, 'semester_id' => $semesterId] = $this->context->requireContext();

        $targetYearId = $request->filled('to_academic_year_id')
            ? $request->integer('to_academic_year_id')
            : null;

        $classId = $request->filled('class_id') ? $request->integer('class_id') : null;

        return $this->render('Promotion::Promotions/Create', [
            'students' => $eligibility->eligible($sourceYearId, $semesterId, $classId),

            /*
             * Offered in full rather than narrowed by the target year: terms
             * and classes are standing structures here, so all active ones are
             * valid destinations whichever year is being promoted into.
             */
            // Occupancy is counted for the year being promoted into, so the
            // remaining-seat figures match what the Action will accept.
            'targetClasses' => $eligibility->targetClasses($targetYearId),
            'targetSemesters' => $this->targetSemesterOptions(),

            'sourceClasses' => $this->sourceClassOptions(),
            'academicYears' => $this->academicYearOptions($sourceYearId),
            'statusOptions' => $this->statusOptions(),
            'filters' => [
                'to_academic_year_id' => $targetYearId,
                'class_id' => $classId,
            ],
            'context' => [
                'academic_year_id' => $sourceYearId,
                'academic_year_name' => AcademicYear::query()->whereKey($sourceYearId)->value('name'),
            ],
        ]);
    }

    public function store(StorePromotionRequest $request, PromoteStudentsAction $action): RedirectResponse
    {
        $this->authorize('create', StudentPromotion::class);

        $records = $action->execute(PromoteStudentsDTO::fromRequest($request));

        return $this->toRoute(
            'promotions.index',
            [],
            __('promotion::promotions.promoted', ['count' => $records->count()]),
        );
    }

    public function revert(StudentPromotion $promotion, RevertPromotionAction $action): RedirectResponse
    {
        $this->authorize('revert', $promotion);

        $action->execute($promotion);

        return $this->toRoute('promotions.index', [], __('promotion::promotions.reverted'));
    }

    /**
     * Years a promotion may target — every active year except the one being
     * promoted out of.
     *
     * @return array<int, array{value: int, title: string}>
     */
    private function academicYearOptions(?int $excludeId = null): array
    {
        return AcademicYear::query()
            ->where('is_active', true)
            ->when($excludeId !== null, fn ($query) => $query->whereKeyNot($excludeId))
            ->orderByDesc('start_date')
            ->get(['id', 'name'])
            ->map(fn (AcademicYear $year): array => [
                'value' => $year->id,
                'title' => $year->name,
            ])
            ->all();
    }

    /**
     * Every active term, in teaching order.
     *
     * Not scoped to the target year — see targetClasses(): terms recur each
     * year, so the administrator picks the year and the term independently.
     *
     * @return array<int, array{value: int, title: string}>
     */
    private function targetSemesterOptions(): array
    {
        return Semester::query()
            ->where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get()
            ->map(fn (Semester $semester): array => [
                'value' => $semester->id,

                // `name` is translatable JSON; the model resolves the locale.
                'title' => (string) $semester->name,
            ])
            ->all();
    }

    /**
     * Classes available to narrow the eligible-student list by.
     *
     * Not year-scoped — classes are standing structures. The list is only a
     * filter over students who are already limited to the current year.
     *
     * @return array<int, array{value: int, title: string}>
     */
    private function sourceClassOptions(): array
    {
        return SchoolClass::query()
            ->where('is_active', true)
            ->get(['id', 'name'])
            ->map(fn (SchoolClass $class): array => [
                'value' => $class->id,
                'title' => $class->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{value: string, title: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            static fn (PromotionStatus $status): array => [
                'value' => $status->value,
                'title' => $status->label(),
            ],
            PromotionStatus::cases(),
        );
    }
}
