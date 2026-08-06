<?php

namespace Modules\Meeting\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Meeting\Models\Meeting;

/**
 * @mixin Meeting
 */
class MeetingResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'description_translations' => $this->getTranslations('description'),
            'room_name' => $this->room_name,
            'meeting_url' => $this->meeting_url,
            'has_password' => filled($this->password),
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'can_manage' => $request->user()?->can('update', $this->resource) ?? false,
            'teacher_id' => $this->teacher_id,
            'branch_id' => $this->branch_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'subject_id' => $this->subject_id,
            'school_class_id' => $this->school_class_id,
            'timetable_session_id' => $this->timetable_session_id,
            'created_at' => $this->created_at?->toIso8601String(),

            'teacher' => $this->whenLoaded('teacher', fn () => $this->teacher ? [
                'id' => $this->teacher->id,
                'name' => $this->teacher->name,
            ] : null),

            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),

            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),

            'semester' => $this->whenLoaded('semester', fn () => $this->semester ? [
                'id' => $this->semester->id,
                'name' => $this->semester->name,
            ] : null),

            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ] : null),

            'school_class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass ? [
                'id' => $this->schoolClass->id,
                'name' => $this->schoolClass->name,
            ] : null),
        ];
    }
}
