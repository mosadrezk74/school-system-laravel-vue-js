<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Finance\Actions\ArchiveStudentFeeAction;
use Modules\Finance\Actions\AssignStudentFeeAction;
use Modules\Finance\Actions\RestoreStudentFeeAction;
use Modules\Finance\Actions\UpdateStudentFeeAction;
use Modules\Finance\DTOs\CreateStudentFeeDTO;
use Modules\Finance\DTOs\StudentFeeFilterDTO;
use Modules\Finance\DTOs\UpdateStudentFeeDTO;
use Modules\Finance\Enums\StudentFeeStatus;
use Modules\Finance\Http\Requests\StoreStudentFeeRequest;
use Modules\Finance\Http\Requests\UpdateStudentFeeRequest;
use Modules\Finance\Models\FeeStructure;
use Modules\Finance\Models\StudentFee;
use Modules\Finance\Queries\StudentFeeQuery;
use Modules\Finance\Transformers\StudentFeeResource;
use Modules\Student\Models\StudentProfile;

class StudentFeeController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', StudentFee::class);

        $filters = StudentFeeFilterDTO::fromRequest($request);

        $studentFees = (new StudentFeeQuery)
            ->studentId($filters->studentId)
            ->feeStructureId($filters->feeStructureId)
            ->status($filters->status)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::StudentFees/Index', [
            'studentFees' => StudentFeeResource::collection($studentFees),
            'filters' => $request->only(['student_id', 'fee_structure_id', 'status']),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StudentFee::class);

        return $this->render('Finance::StudentFees/Create', $this->lookups());
    }

    public function store(StoreStudentFeeRequest $request, AssignStudentFeeAction $action): RedirectResponse
    {
        $this->authorize('create', StudentFee::class);

        $action->execute(CreateStudentFeeDTO::fromRequest($request));

        return $this->toRoute('student-fees.index', [], __('finance::finance.student_fee_assigned'));
    }

    public function show(StudentFee $studentFee): Response
    {
        $this->authorize('view', $studentFee);

        $studentFee->load(['student.user', 'feeStructure.feeCategory']);

        return $this->render('Finance::StudentFees/Show', [
            'studentFee' => new StudentFeeResource($studentFee),
        ]);
    }

    public function edit(StudentFee $studentFee): Response
    {
        $this->authorize('update', $studentFee);

        $studentFee->load(['student.user', 'feeStructure.feeCategory']);

        return $this->render('Finance::StudentFees/Edit', [
            'studentFee' => new StudentFeeResource($studentFee),
            ...$this->lookups(),
        ]);
    }

    public function update(UpdateStudentFeeRequest $request, StudentFee $studentFee, UpdateStudentFeeAction $action): RedirectResponse
    {
        $this->authorize('update', $studentFee);

        $action->execute(['studentFee' => $studentFee, 'dto' => UpdateStudentFeeDTO::fromRequest($request)]);

        return $this->toRoute('student-fees.index', [], __('finance::finance.student_fee_updated'));
    }

    public function destroy(StudentFee $studentFee, ArchiveStudentFeeAction $action): RedirectResponse
    {
        $this->authorize('delete', $studentFee);

        $action->execute($studentFee);

        return $this->toRoute('student-fees.index', [], __('finance::finance.student_fee_archived'));
    }

    public function restore(int $studentFee, RestoreStudentFeeAction $action): RedirectResponse
    {
        $studentFee = StudentFee::withTrashed()->findOrFail($studentFee);

        $this->authorize('restore', $studentFee);

        $action->execute($studentFee);

        return $this->toRoute('student-fees.index', [], __('finance::finance.student_fee_restored'));
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'students' => StudentProfile::query()
                ->where('academic_year_id', AcademicContextService::yearId())
                ->where('is_active', true)
                ->with('user:id,name')
                ->get(['id', 'user_id', 'admission_number'])
                ->map(fn (StudentProfile $student) => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'admission_number' => $student->admission_number,
                ])->values(),
            'feeStructures' => FeeStructure::query()->with('feeCategory')->get()
                ->map(fn (FeeStructure $feeStructure) => [
                    'id' => $feeStructure->id,
                    'name' => $feeStructure->name,
                    'amount' => $feeStructure->amount,
                ])->values(),
            'statuses' => StudentFeeStatus::options(),
        ];
    }
}
