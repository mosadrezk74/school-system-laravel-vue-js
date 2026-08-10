<?php

namespace Modules\Finance\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Finance\Models\FeeStructure;

/**
 * @mixin FeeStructure
 */
class FeeStructureResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fee_category_id' => $this->fee_category_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'grade_level_id' => $this->grade_level_id,
            'classroom_id' => $this->classroom_id,
            'is_active' => (bool) $this->is_active,
            'name' => $this->name,
            'amount' => $this->amount,
            'frequency' => $this->frequency?->value,
            'frequency_label' => $this->frequency?->label(),
            'is_optional' => (bool) $this->is_optional,
            'due_day' => $this->due_day,
            'late_fee_amount' => $this->late_fee_amount,
            'late_fee_days' => $this->late_fee_days,
            'created_at' => $this->created_at?->toIso8601String(),

            'fee_category' => $this->whenLoaded('feeCategory', fn () => $this->feeCategory ? [
                'id' => $this->feeCategory->id,
                'name' => $this->feeCategory->name,
            ] : null),

            'grade_level' => $this->whenLoaded('gradeLevel', fn () => $this->gradeLevel ? [
                'id' => $this->gradeLevel->id,
                'name' => $this->gradeLevel->name,
            ] : null),

            'classroom' => $this->whenLoaded('classroom', fn () => $this->classroom ? [
                'id' => $this->classroom->id,
                'name' => $this->classroom->name,
            ] : null),

            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),

            'semester' => $this->whenLoaded('semester', fn () => $this->semester ? [
                'id' => $this->semester->id,
                'name' => $this->semester->name,
            ] : null),

            /*
             * The period and its length come from the model, which reads them
             * off the semester or year. The client renders these; it never
             * works the duration out for itself.
             */
            'subscription_period' => $this->whenLoaded('semester', function (): array {
                ['start' => $start, 'end' => $end] = $this->subscriptionPeriod();

                return [
                    'start_date' => $start?->toDateString(),
                    'end_date' => $end?->toDateString(),
                    'duration_months' => $this->durationInMonths(),
                    'is_term' => $this->isTermSubscription(),
                    'is_full_year' => $this->isFullYearSubscription(),
                ];
            }),
        ];
    }
}
