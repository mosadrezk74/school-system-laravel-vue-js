<?php

namespace Modules\Timetable\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Modules\Core\Abstracts\BaseRequest;
use Modules\SchoolClass\Models\ClassSubject;
use Modules\Setting\Services\SettingsService;
use Modules\Teacher\Models\TeacherProfile;
use Modules\Timetable\Enums\Weekday;
use Modules\Timetable\Models\Timetable;

class UpdateTimetableEntriesRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entries' => ['present', 'array'],
            'entries.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'entries.*.period_id' => ['required', 'integer', 'exists:periods,id'],
            'entries.*.is_break' => ['sometimes', 'boolean'],
            'entries.*.is_online' => ['sometimes', 'boolean'],
            'entries.*.subject_id' => ['required_if:entries.*.is_break,false', 'nullable', 'integer', 'exists:subjects,id'],
            'entries.*.teacher_id' => ['required_if:entries.*.is_break,false', 'nullable', 'integer', 'exists:users,id'],
            'entries.*.classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
        ];
    }

    /**
     * Rules that need to look across the whole payload or hit the database:
     * the day must be one the school actually works, and the teacher must be
     * qualified for the subject they are assigned to. Neither can be trusted
     * to the frontend, which only filters the dropdowns for convenience.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateWorkingDays($validator);
            $this->validateTeacherTeachesSubject($validator);
            $this->validateClassSubjectAssignment($validator);
            $this->validateNoDuplicateSlots($validator);
        });
    }

    /**
     * Where the class already declares which subjects it studies, a timetable
     * entry may not invent a different one, and a class_subjects row naming a
     * teacher is the authority on who teaches it.
     */
    private function validateClassSubjectAssignment(Validator $validator): void
    {
        $timetable = $this->route('timetable');

        if (! $timetable instanceof Timetable) {
            return;
        }

        $assignments = ClassSubject::query()
            ->where('class_id', $timetable->class_id)
            ->get();

        if ($assignments->isEmpty()) {
            return;
        }

        foreach ($this->entriesPayload() as $index => $entry) {
            if (($entry['is_break'] ?? false) || empty($entry['subject_id'])) {
                continue;
            }

            $match = $assignments->firstWhere('subject_id', (int) $entry['subject_id']);

            if ($match === null) {
                $validator->errors()->add(
                    "entries.{$index}.subject_id",
                    __('timetable::timetable.validation.subject_not_taught_by_class')
                );

                continue;
            }

            if ($match->teacher_id !== null
                && ! empty($entry['teacher_id'])
                && (int) $entry['teacher_id'] !== $match->teacher_id) {
                $validator->errors()->add(
                    "entries.{$index}.teacher_id",
                    __('timetable::timetable.validation.teacher_not_assigned_to_class_subject')
                );
            }
        }
    }

    /**
     * The unique index catches this at write time; surfacing it here attaches
     * the failure to the offending cell instead of a database error.
     */
    private function validateNoDuplicateSlots(Validator $validator): void
    {
        $seen = [];

        foreach ($this->entriesPayload() as $index => $entry) {
            if (! isset($entry['day_of_week'], $entry['period_id'])) {
                continue;
            }

            $slot = $entry['day_of_week'].'-'.$entry['period_id'];

            if (isset($seen[$slot])) {
                $validator->errors()->add(
                    "entries.{$index}.period_id",
                    __('timetable::timetable.validation.duplicate_slot')
                );
            }

            $seen[$slot] = true;
        }
    }

    private function validateWorkingDays(Validator $validator): void
    {
        /** @var array<int, int> $activeWeekdays */
        $activeWeekdays = app(SettingsService::class)->value('active_weekdays', Weekday::values());

        foreach ($this->entriesPayload() as $index => $entry) {
            if (! isset($entry['day_of_week'])) {
                continue;
            }

            if (! in_array((int) $entry['day_of_week'], $activeWeekdays, true)) {
                $validator->errors()->add(
                    "entries.{$index}.day_of_week",
                    __('timetable::timetable.validation.not_a_working_day')
                );
            }
        }
    }

    private function validateTeacherTeachesSubject(Validator $validator): void
    {
        $entries = $this->entriesPayload();

        $teacherIds = array_filter(array_column($entries, 'teacher_id'));

        if ($teacherIds === []) {
            return;
        }

        $subjectIdsByTeacher = TeacherProfile::query()
            ->whereIn('user_id', $teacherIds)
            ->with('subjects:id')
            ->get()
            ->mapWithKeys(fn (TeacherProfile $teacher): array => [
                $teacher->user_id => $teacher->subjects->pluck('id')->all(),
            ]);

        foreach ($entries as $index => $entry) {
            if (($entry['is_break'] ?? false) || empty($entry['teacher_id']) || empty($entry['subject_id'])) {
                continue;
            }

            $qualified = $subjectIdsByTeacher->get((int) $entry['teacher_id'], []);

            if (! in_array((int) $entry['subject_id'], $qualified, true)) {
                $validator->errors()->add(
                    "entries.{$index}.teacher_id",
                    __('timetable::timetable.validation.teacher_does_not_teach_subject')
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entriesPayload(): array
    {
        $entries = $this->input('entries', []);

        return is_array($entries) ? $entries : [];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'entries.*.subject_id' => __('timetable::timetable.fields.subject'),
            'entries.*.teacher_id' => __('timetable::timetable.fields.teacher'),
            'entries.*.classroom_id' => __('timetable::timetable.fields.classroom'),
        ];
    }
}
