<?php

namespace Modules\Subject\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseDTO;

final class CreateSubjectDTO extends BaseDTO
{
    public function __construct(
        public ?int $branchId,
        public ?string $code,
        public array $name,
        public ?array $description,
        public ?string $shortName,
        public string $subjectType,
        public float $passGrade,
        public int $creditHours,
        public float $maxMarks,
        public string $color,
        public ?string $icon,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            branchId: $request->validated('branch_id') ? (int) $request->validated('branch_id') : null,
            code: $request->validated('code'),
            name: $request->validated('name'),
            description: $request->validated('description'),
            shortName: $request->validated('short_name'),
            subjectType: $request->validated('subject_type', 'core'),
            passGrade: (float) $request->validated('pass_grade', 60),
            creditHours: (int) $request->validated('credit_hours', 1),
            maxMarks: (float) $request->validated('max_marks', 100),
            color: $request->validated('color', '#2563eb'),
            icon: $request->validated('icon'),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    public function toArray(): array
    {
        return [
            'branch_id' => $this->branchId,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'short_name' => $this->shortName,
            'subject_type' => $this->subjectType,
            'pass_grade' => $this->passGrade,
            'credit_hours' => $this->creditHours,
            'max_marks' => $this->maxMarks,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => $this->isActive,
        ];
    }
}
