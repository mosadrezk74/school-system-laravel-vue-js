<?php

namespace Modules\Promotion\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Promotion\Models\StudentPromotion;

/**
 * @mixin StudentPromotion
 */
class PromotionResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->whenLoaded('student', fn () => $this->student?->user?->name),
            'admission_number' => $this->whenLoaded('student', fn () => $this->student?->admission_number),

            'from_academic_year_id' => $this->from_academic_year_id,
            'from_academic_year_name' => $this->whenLoaded('fromAcademicYear', fn () => $this->fromAcademicYear?->name),
            'to_academic_year_id' => $this->to_academic_year_id,
            'to_academic_year_name' => $this->whenLoaded('toAcademicYear', fn () => $this->toAcademicYear?->name),

            'from_class_id' => $this->from_class_id,
            'from_class_name' => $this->whenLoaded('fromClass', fn () => $this->fromClass?->name),
            'to_class_id' => $this->to_class_id,
            'to_class_name' => $this->whenLoaded('toClass', fn () => $this->toClass?->name),

            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),

            'was_graduated' => $this->was_graduated,
            'promoted_by' => $this->whenLoaded('promotedBy', fn () => $this->promotedBy?->name),
            'promoted_at' => $this->promoted_at?->toIso8601String(),
            'remarks' => $this->remarks,

            /*
             * Only the student's most recent decision may be undone, so the
             * server decides where the UI is allowed to offer Revert.
             */
            'can_revert' => $this->isLatestForStudent(),
        ];
    }

    private function isLatestForStudent(): bool
    {
        return ! StudentPromotion::query()
            ->where('student_id', $this->student_id)
            ->where(function ($query): void {
                $query->where('promoted_at', '>', $this->promoted_at)
                    ->orWhere(function ($inner): void {
                        $inner->where('promoted_at', $this->promoted_at)
                            ->where('id', '>', $this->id);
                    });
            })
            ->exists();
    }
}
