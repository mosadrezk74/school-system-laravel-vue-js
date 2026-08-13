<?php

namespace Modules\Attendance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Models\Semester;
use Modules\Academic\Services\AcademicContextService;
use Modules\Attendance\Actions\BuildAttendanceDashboardAction;
use Modules\Attendance\Actions\BuildStudentAttendanceHistoryAction;
use Modules\Attendance\Actions\DeleteAttendanceRecordAction;
use Modules\Attendance\Actions\GenerateAttendanceReportAction;
use Modules\Attendance\Actions\MarkAttendanceAction;
use Modules\Attendance\DTOs\AttendanceDashboardFilterDTO;
use Modules\Attendance\DTOs\AttendanceFilterDTO;
use Modules\Attendance\DTOs\AttendanceReportFilterDTO;
use Modules\Attendance\DTOs\MarkAttendanceDTO;
use Modules\Attendance\DTOs\StudentAttendanceFilterDTO;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Http\Requests\AttendanceReportRequest;
use Modules\Attendance\Http\Requests\StoreAttendanceRequest;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Attendance\Queries\AttendanceQuery;
use Modules\Attendance\Services\AttendanceRosterService;
use Modules\Attendance\Transformers\AttendanceRecordResource;
use Modules\Attendance\Transformers\AttendanceReportRowResource;
use Modules\Attendance\Transformers\StudentAttendanceProfileResource;
use Modules\Attendance\Transformers\StudentAttendanceTimelineResource;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Student\Models\StudentProfile;
use Modules\Subject\Models\Subject;

class AttendanceController extends BaseInertiaController
{
    public function dashboard(Request $request, BuildAttendanceDashboardAction $action): Response
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $filter = AttendanceDashboardFilterDTO::fromRequest($request);

        return $this->render('Attendance::Dashboard/Index', [
            ...$this->reportOptions(),
            'filters' => $request->only(['class_id', 'semester_id']),
            'dashboard' => $action->execute($filter),
        ]);
    }

    public function studentHistory(Request $request, StudentProfile $student, BuildStudentAttendanceHistoryAction $action): Response
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $history = $action->execute(StudentAttendanceFilterDTO::fromRequest($request, $student->id));

        return $this->render('Attendance::Students/Show', [
            'semesters' => Semester::query()->where('academic_year_id', AcademicContextService::yearId())->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                ])
                ->values(),
            'filters' => $request->only(['semester_id', 'date_from', 'date_to']),
            'student' => new StudentAttendanceProfileResource($history['student']),
            'summary' => $history['summary']->toArray(),
            'timeline' => StudentAttendanceTimelineResource::collection($history['timeline']),
        ]);
    }

    public function reportForm(Request $request): Response
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        return $this->render('Attendance::Report/Index', [
            ...$this->reportOptions(),
            'filters' => $request->only(['class_id', 'semester_id', 'date_from', 'date_to']),
            'generated' => false,
            'rows' => AttendanceReportRowResource::collection(collect()),
            'statistics' => null,
        ]);
    }

    public function report(AttendanceReportRequest $request, GenerateAttendanceReportAction $action): Response
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $report = $action->execute(AttendanceReportFilterDTO::fromRequest($request));

        return $this->render('Attendance::Report/Index', [
            ...$this->reportOptions(),
            'filters' => $request->only(['class_id', 'semester_id', 'date_from', 'date_to']),
            'generated' => true,
            'rows' => AttendanceReportRowResource::collection($report['rows']),
            'statistics' => $report['statistics'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function reportOptions(): array
    {
        return [
            'classes' => $this->activeClasses(),
            'semesters' => Semester::query()->where('academic_year_id', AcademicContextService::yearId())->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                ])
                ->values(),
        ];
    }

    public function take(Request $request, AttendanceRosterService $roster): Response
    {
        $this->authorize('create', AttendanceRecord::class);

        $classId = $request->filled('class_id') ? (int) $request->input('class_id') : null;
        $date = $request->filled('date') ? $request->date('date')->toDateString() : now()->toDateString();

        return $this->render('Attendance::Take/Index', [
            'classes' => $this->activeClasses(),
            'statuses' => AttendanceStatus::options(),
            'filters' => [
                'class_id' => $classId,
                'date' => $date,
            ],
            'roster' => $classId ? $roster->forClassAndDate($classId, $date) : [],
        ]);
    }

    public function storeTaken(StoreAttendanceRequest $request, MarkAttendanceAction $action): RedirectResponse
    {
        $this->authorize('create', AttendanceRecord::class);

        $action->execute(MarkAttendanceDTO::fromRequest($request));

        return $this->backWithSuccess(__('attendance::attendance.marked'));
    }

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
                'class_id', 'subject_id', 'semester_id', 'session', 'date_from', 'date_to',
            ]),
            'classes' => $this->activeClasses(),
            'subjects' => $this->subjectOptions(),
            'semesters' => Semester::query()->where('academic_year_id', AcademicContextService::yearId())->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                ])
                ->values(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', AttendanceRecord::class);

        return $this->render('Attendance::Records/Create', [
            'classes' => $this->activeClasses(),
            'subjects' => $this->subjectOptions(),
            'semesters' => Semester::query()->where('academic_year_id', AcademicContextService::yearId())->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                ])
                ->values(),
            'roster' => $request->filled('class_id') ? $this->rosterForClass((int) $request->input('class_id')) : [],

            /*
             * From the same enum the rule validates against. The form kept its
             * own list, which had drifted: it never offered `other`, even
             * though StoreAttendanceRequest accepts it.
             */
            'attendanceStatuses' => AttendanceStatus::selectOptions(),
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
            'subjects' => $this->subjectOptions(),
            'semesters' => Semester::query()->where('academic_year_id', AcademicContextService::yearId())->get()
                ->map(fn (Semester $semester) => [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'academic_year_id' => $semester->academic_year_id,
                ])
                ->values(),
            'attendanceStatuses' => AttendanceStatus::selectOptions(),
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
     * @return array<int, array<string, mixed>>
     */
    protected function activeClasses(): array
    {
        return SchoolClass::query()->where('is_active', true)
            ->with(['gradeLevel:id,name'])
            ->get(['id', 'name', 'grade_level_id'])
            ->map(fn (SchoolClass $class) => [
                'id' => $class->id,
                'name' => $class->name,
                'grade_level_id' => $class->grade_level_id,
                'grade_level' => $class->gradeLevel ? ['id' => $class->gradeLevel->id, 'name' => $class->gradeLevel->name] : null,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    protected function subjectOptions(): array
    {
        return Subject::query()->get()
            ->map(fn (Subject $subject) => ['id' => $subject->id, 'name' => $subject->name])
            ->values()
            ->all();
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
