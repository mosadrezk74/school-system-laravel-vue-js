<?php

namespace Modules\Attendance\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\Semester;
use Modules\Attendance\Actions\DeleteAttendanceRecordAction;
use Modules\Attendance\Actions\MarkAttendanceAction;
use Modules\Attendance\DTOs\AttendanceFilterDTO;
use Modules\Attendance\DTOs\MarkAttendanceDTO;
use Modules\Attendance\Http\Requests\StoreAttendanceRequest;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Attendance\Queries\AttendanceQuery;
use Modules\Attendance\Transformers\AttendanceRecordResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Modules\Subject\Models\Subject;

class AttendanceController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $filters = AttendanceFilterDTO::fromRequest($request);

        $query = (new AttendanceQuery)
            ->classId($filters->classId)
            ->subjectId($filters->subjectId)
            ->academicYearId($filters->academicYearId)
            ->semesterId($filters->semesterId)
            ->session($filters->session)
            ->dateBetween($filters->dateFrom, $filters->dateTo)
            ->sort($filters->sortBy, $filters->sortOrder);

        $records = $query->paginate($filters->perPage);

        return $this->render('Attendance::Records/Index', [
            'records' => AttendanceRecordResource::collection($records),
            'filters' => $request->only([
                'class_id', 'subject_id', 'academic_year_id', 'semester_id', 'session', 'date_from', 'date_to',
            ]),
            'classes' => $this->activeClasses(),
            'subjects' => Subject::query()->get(['id', 'name']),
            'academicYears' => AcademicYear::query()->where('is_active', true)->get(['id', 'name']),
            'semesters' => Semester::query()->get(['id', 'name', 'academic_year_id']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', AttendanceRecord::class);

        return $this->render('Attendance::Records/Create', [
            'classes' => $this->activeClasses(),
            'subjects' => Subject::query()->get(['id', 'name']),
            'academicYears' => AcademicYear::query()->where('is_active', true)->get(['id', 'name']),
            'semesters' => Semester::query()->get(['id', 'name', 'academic_year_id']),
            'roster' => $request->filled('class_id') ? $this->rosterForClass((int) $request->input('class_id')) : [],
        ]);
    }

    public function store(StoreAttendanceRequest $request, MarkAttendanceAction $action): RedirectResponse
    {
        $this->authorize('create', AttendanceRecord::class);

        $action->execute(MarkAttendanceDTO::fromRequest($request));

        return $this->toRoute('attendance.records.index', [], __('attendance::attendance.marked'));
    }

    public function show(AttendanceRecord $record): Response
    {
        $this->authorize('view', $record);

        $record->load(['class', 'subject', 'academicYear', 'semester', 'details.student.user']);

        return $this->render('Attendance::Records/Show', [
            'record' => new AttendanceRecordResource($record),
        ]);
    }

    public function edit(AttendanceRecord $record): Response
    {
        $this->authorize('create', $record);

        $record->load(['class', 'subject', 'academicYear', 'semester', 'details.student.user']);

        $statusesByStudent = $record->details->keyBy('student_id');

        $roster = StudentProfile::query()
            ->where('current_class_id', $record->class_id)
            ->where('is_active', true)
            ->with('user')
            ->get()
            ->map(fn (StudentProfile $student) => [
                'student_id' => $student->id,
                'name' => $student->user?->name,
                'admission_number' => $student->admission_number,
                'status' => $statusesByStudent->get($student->id)?->status ?? 'present',
                'check_in_time' => $statusesByStudent->get($student->id)?->check_in_time,
                'check_out_time' => $statusesByStudent->get($student->id)?->check_out_time,
            ])
            ->values();

        return $this->render('Attendance::Records/Edit', [
            'record' => new AttendanceRecordResource($record),
            'roster' => $roster,
            'classes' => $this->activeClasses(),
            'subjects' => Subject::query()->get(['id', 'name']),
            'academicYears' => AcademicYear::query()->where('is_active', true)->get(['id', 'name']),
            'semesters' => Semester::query()->get(['id', 'name', 'academic_year_id']),
        ]);
    }

    public function update(StoreAttendanceRequest $request, AttendanceRecord $record, MarkAttendanceAction $action): RedirectResponse
    {
        $this->authorize('create', $record);

        $action->execute(MarkAttendanceDTO::fromRequest($request));

        return $this->toRoute('attendance.records.index', [], __('attendance::attendance.updated'));
    }

    public function destroy(AttendanceRecord $record, DeleteAttendanceRecordAction $action): RedirectResponse
    {
        $this->authorize('delete', $record);

        $action->execute($record);

        return $this->toRoute('attendance.records.index', [], __('attendance::attendance.deleted'));
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    protected function activeClasses()
    {
        return SchoolClass::query()->where('is_active', true)
            ->with(['gradeLevel:id,name', 'branch:id,name'])
            ->get(['id', 'name', 'section_code', 'grade_level_id', 'academic_year_id', 'branch_id']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function rosterForClass(int $classId): array
    {
        return StudentProfile::query()
            ->where('current_class_id', $classId)
            ->where('is_active', true)
            ->with('user')
            ->get()
            ->map(fn (StudentProfile $student) => [
                'student_id' => $student->id,
                'name' => $student->user?->name,
                'admission_number' => $student->admission_number,
                'status' => 'present',
                'check_in_time' => null,
                'check_out_time' => null,
            ])
            ->values()
            ->all();
    }
}
