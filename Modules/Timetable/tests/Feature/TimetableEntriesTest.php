<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Auth\Database\Seeders\RolePermissionSeeder;
use Modules\Auth\Enums\UserRole;
use Modules\Classroom\Models\Classroom;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\Setting\Models\SchoolSetting;
use Modules\Subject\Models\Subject;
use Modules\Teacher\Models\TeacherProfile;
use Modules\Timetable\Enums\Weekday;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Models\Timetable;
use Modules\Timetable\Models\TimetableEntry;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->academicYear = AcademicYear::factory()->current()->create();

    // The school works Sunday–Wednesday; Thursday is deliberately excluded so
    // the non-working-day rule has something to reject.
    $this->workingDays = [
        Weekday::Sunday->value,
        Weekday::Monday->value,
        Weekday::Tuesday->value,
        Weekday::Wednesday->value,
    ];

    SchoolSetting::create([
        'school_name' => 'Athr',
        'school_code' => 'ATHR',
        'setup_completed' => true,
        'settings' => ['active_weekdays' => $this->workingDays],
    ]);
});

/**
 * Sections share one grade level, mirroring the real structure where
 * Grade 3 - A and Grade 3 - B both hang off a single Grade 3.
 */
function makeClass(AcademicYear $year, string $name = 'Grade 3 - A'): SchoolClass
{
    $grade = GradeLevel::query()->firstOr(fn () => GradeLevel::create([
        'name' => ['en' => 'Grade 3'],
        'is_active' => true,
    ]));

    return SchoolClass::create([
        'grade_level_id' => $grade->id,
        'academic_year_id' => $year->id,
        'name' => $name,
        'capacity' => 30,
        'is_active' => true,
    ]);
}

function makeTimetableSubject(string $name): Subject
{
    return Subject::create(['name' => ['en' => $name], 'is_active' => true]);
}

/** Creates a teacher qualified for the given subjects, returning the user. */
function makeTeacherFor(array $subjects, string $name = 'Ahmed Ali'): User
{
    $user = User::factory()->create(['role' => UserRole::Teacher, 'name' => $name]);
    $profile = TeacherProfile::create(['user_id' => $user->id, 'is_active' => true]);
    $profile->subjects()->sync(collect($subjects)->pluck('id')->all());

    return $user;
}

function makePeriod(int $number): Period
{
    $start = sprintf('%02d:00:00', 7 + $number);

    return Period::create([
        'period_number' => $number,
        'name' => ['en' => "Period {$number}"],
        'start_time' => $start,
        'end_time' => sprintf('%02d:45:00', 7 + $number),
        'is_break' => false,
        'is_active' => true,
    ]);
}

function makeTimetableFor(SchoolClass $class, AcademicYear $year): Timetable
{
    return Timetable::create([
        'class_id' => $class->id,
        'academic_year_id' => $year->id,
        'is_active' => true,
    ]);
}

test('an admin can create a full week of entries for a class in one request', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $arabic = makeTimetableSubject('Arabic');
    $teacher = makeTeacherFor([$math, $arabic]);

    $first = makePeriod(1);
    $second = makePeriod(2);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetable), [
            'entries' => [
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => $first->id, 'subject_id' => $math->id, 'teacher_id' => $teacher->id],
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => $second->id, 'subject_id' => $arabic->id, 'teacher_id' => $teacher->id],
                ['day_of_week' => Weekday::Monday->value, 'period_id' => $first->id, 'subject_id' => $arabic->id, 'teacher_id' => $teacher->id],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('timetables.edit', $timetable));

    expect($timetable->entries()->count())->toBe(3);

    // Period metadata is denormalised onto the entry from the Period row.
    $entry = $timetable->entries()->where('period_id', $first->id)->where('day_of_week', Weekday::Sunday->value)->first();
    expect($entry->period_number)->toBe($first->period_number)
        ->and($entry->start_time)->toBe($first->start_time);
});

test('a teacher who does not teach the subject is rejected', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $art = makeTimetableSubject('Art');
    $artTeacher = makeTeacherFor([$art], 'Art Teacher');
    $period = makePeriod(1);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetable), [
            'entries' => [
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => $period->id, 'subject_id' => $math->id, 'teacher_id' => $artTeacher->id],
            ],
        ])
        ->assertSessionHasErrors('entries.0.teacher_id');

    expect($timetable->entries()->count())->toBe(0);
});

test('a teacher qualified for several subjects is accepted for each of them', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $science = makeTimetableSubject('Science');
    $teacher = makeTeacherFor([$math, $science]);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetable), [
            'entries' => [
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => makePeriod(1)->id, 'subject_id' => $math->id, 'teacher_id' => $teacher->id],
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => makePeriod(2)->id, 'subject_id' => $science->id, 'teacher_id' => $teacher->id],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($timetable->entries()->count())->toBe(2);
});

test('a day outside the school working days is rejected', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $teacher = makeTeacherFor([$math]);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetable), [
            'entries' => [
                ['day_of_week' => Weekday::Thursday->value, 'period_id' => makePeriod(1)->id, 'subject_id' => $math->id, 'teacher_id' => $teacher->id],
            ],
        ])
        ->assertSessionHasErrors('entries.0.day_of_week');

    expect($timetable->entries()->count())->toBe(0);
});

test('a subject that does not exist is rejected', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $teacher = makeTeacherFor([makeTimetableSubject('Mathematics')]);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetable), [
            'entries' => [
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => makePeriod(1)->id, 'subject_id' => 999999, 'teacher_id' => $teacher->id],
            ],
        ])
        ->assertSessionHasErrors('entries.0.subject_id');
});

test('the same teacher cannot be booked for two classes in the same day and period', function () {
    $math = makeTimetableSubject('Mathematics');
    $teacher = makeTeacherFor([$math]);
    $period = makePeriod(1);

    $classA = makeClass($this->academicYear, 'Grade 3 - A');
    $timetableA = makeTimetableFor($classA, $this->academicYear);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetableA), [
            'entries' => [
                ['day_of_week' => Weekday::Monday->value, 'period_id' => $period->id, 'subject_id' => $math->id, 'teacher_id' => $teacher->id],
            ],
        ])->assertSessionHasNoErrors();

    $classB = makeClass($this->academicYear, 'Grade 3 - B');
    $timetableB = makeTimetableFor($classB, $this->academicYear);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetableB), [
            'entries' => [
                ['day_of_week' => Weekday::Monday->value, 'period_id' => $period->id, 'subject_id' => $math->id, 'teacher_id' => $teacher->id],
            ],
        ])->assertSessionHasErrors();

    expect($timetableB->entries()->count())->toBe(0)
        ->and($timetableA->entries()->count())->toBe(1);
});

test('the same classroom cannot be booked for two classes in the same day and period', function () {
    $math = makeTimetableSubject('Mathematics');
    $teacherA = makeTeacherFor([$math], 'Teacher A');
    $teacherB = makeTeacherFor([$math], 'Teacher B');
    $period = makePeriod(1);
    $room = Classroom::create(['name' => ['en' => 'Room 101'], 'capacity' => 30, 'is_active' => true]);

    $timetableA = makeTimetableFor(makeClass($this->academicYear, 'Grade 3 - A'), $this->academicYear);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetableA), [
            'entries' => [
                ['day_of_week' => Weekday::Monday->value, 'period_id' => $period->id, 'subject_id' => $math->id, 'teacher_id' => $teacherA->id, 'classroom_id' => $room->id],
            ],
        ])->assertSessionHasNoErrors();

    $timetableB = makeTimetableFor(makeClass($this->academicYear, 'Grade 3 - B'), $this->academicYear);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetableB), [
            'entries' => [
                ['day_of_week' => Weekday::Monday->value, 'period_id' => $period->id, 'subject_id' => $math->id, 'teacher_id' => $teacherB->id, 'classroom_id' => $room->id],
            ],
        ])->assertSessionHasErrors();

    expect($timetableB->entries()->count())->toBe(0);
});

test('a rejected bulk save leaves the previous week completely intact', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $art = makeTimetableSubject('Art');
    $mathTeacher = makeTeacherFor([$math], 'Math Teacher');
    $artTeacher = makeTeacherFor([$art], 'Art Teacher');
    $first = makePeriod(1);
    $second = makePeriod(2);

    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetable), [
            'entries' => [
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => $first->id, 'subject_id' => $math->id, 'teacher_id' => $mathTeacher->id],
            ],
        ])->assertSessionHasNoErrors();

    $original = $timetable->entries()->get();

    // Second entry is invalid: the art teacher cannot teach maths.
    $this->actingAs($this->admin)
        ->put(route('timetables.entries.update', $timetable), [
            'entries' => [
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => $first->id, 'subject_id' => $math->id, 'teacher_id' => $mathTeacher->id],
                ['day_of_week' => Weekday::Sunday->value, 'period_id' => $second->id, 'subject_id' => $math->id, 'teacher_id' => $artTeacher->id],
            ],
        ])->assertSessionHasErrors();

    expect($timetable->entries()->count())->toBe(1)
        ->and($timetable->entries()->first()->id)->toBe($original->first()->id);
});

test('saving the week again replaces the entries rather than duplicating them', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $science = makeTimetableSubject('Science');
    $teacher = makeTeacherFor([$math, $science]);
    $period = makePeriod(1);

    $payload = fn (int $subjectId): array => [
        'entries' => [
            ['day_of_week' => Weekday::Sunday->value, 'period_id' => $period->id, 'subject_id' => $subjectId, 'teacher_id' => $teacher->id],
        ],
    ];

    $this->actingAs($this->admin)->put(route('timetables.entries.update', $timetable), $payload($math->id));
    $this->actingAs($this->admin)->put(route('timetables.entries.update', $timetable), $payload($science->id))
        ->assertSessionHasNoErrors();

    expect($timetable->entries()->count())->toBe(1)
        ->and($timetable->entries()->first()->subject_id)->toBe($science->id);
});

test('an entry is removed when it is left out of the submitted week', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $teacher = makeTeacherFor([$math]);
    $first = makePeriod(1);
    $second = makePeriod(2);

    $this->actingAs($this->admin)->put(route('timetables.entries.update', $timetable), [
        'entries' => [
            ['day_of_week' => Weekday::Sunday->value, 'period_id' => $first->id, 'subject_id' => $math->id, 'teacher_id' => $teacher->id],
            ['day_of_week' => Weekday::Sunday->value, 'period_id' => $second->id, 'subject_id' => $math->id, 'teacher_id' => $teacher->id],
        ],
    ])->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->put(route('timetables.entries.update', $timetable), [
        'entries' => [
            ['day_of_week' => Weekday::Sunday->value, 'period_id' => $first->id, 'subject_id' => $math->id, 'teacher_id' => $teacher->id],
        ],
    ])->assertSessionHasNoErrors();

    expect($timetable->entries()->count())->toBe(1)
        ->and($timetable->entries()->first()->period_id)->toBe($first->id);
});

test('the same class cannot hold two entries in one day and period', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $teacher = makeTeacherFor([$math]);
    $period = makePeriod(1);

    TimetableEntry::create([
        'timetable_id' => $timetable->id,
        'day_of_week' => Weekday::Sunday->value,
        'period_id' => $period->id,
        'period_number' => $period->period_number,
        'start_time' => $period->start_time,
        'end_time' => $period->end_time,
        'subject_id' => $math->id,
        'teacher_id' => $teacher->id,
    ]);

    expect(fn () => TimetableEntry::create([
        'timetable_id' => $timetable->id,
        'day_of_week' => Weekday::Sunday->value,
        'period_id' => $period->id,
        'period_number' => $period->period_number,
        'start_time' => $period->start_time,
        'end_time' => $period->end_time,
        'subject_id' => $math->id,
        'teacher_id' => $teacher->id,
    ]))->toThrow(QueryException::class);
});

test('the edit screen only offers weekdays the school actually works', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $this->actingAs($this->admin)
        ->get(route('timetables.edit', $timetable))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('weekdays', count($this->workingDays))
            ->where('weekdays.0.value', Weekday::Sunday->value)
        );
});

test('the edit screen sends each teacher with the subjects they teach', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $math = makeTimetableSubject('Mathematics');
    $teacher = makeTeacherFor([$math]);

    $this->actingAs($this->admin)
        ->get(route('timetables.edit', $timetable))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('teachers.0.id', $teacher->id)
            ->where('teachers.0.subject_ids', [$math->id])
        );
});

test('a class with no entries yet reports an empty week', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $this->actingAs($this->admin)
        ->get(route('timetables.edit', $timetable))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('timetable.entries', 0));
});

test('a guardian cannot save a timetable', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $guardian = User::factory()->create(['role' => UserRole::Guardian, 'is_active' => true]);
    $guardian->assignRole('guardian');

    $this->actingAs($guardian)
        ->put(route('timetables.entries.update', $timetable), ['entries' => []])
        ->assertForbidden();
});

test('a student cannot save a timetable', function () {
    $class = makeClass($this->academicYear);
    $timetable = makeTimetableFor($class, $this->academicYear);

    $student = User::factory()->create(['role' => UserRole::Student, 'is_active' => true]);
    $student->assignRole('student');

    $this->actingAs($student)
        ->put(route('timetables.entries.update', $timetable), ['entries' => []])
        ->assertForbidden();
});
