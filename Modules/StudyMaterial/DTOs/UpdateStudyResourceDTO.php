<?php

namespace Modules\StudyMaterial\DTOs;

use Illuminate\Http\UploadedFile;
use Modules\StudyMaterial\Http\Requests\UpdateStudyResourceRequest;

final readonly class UpdateStudyResourceDTO
{
    public function __construct(
        public array $title,
        public ?array $description,
        public int $subjectId,
        public int $gradeLevelId,
        public ?int $classroomId,
        public int $teacherId,
        public string $type,
        public ?string $externalUrl,
        public ?UploadedFile $file,
    ) {}

    public static function fromRequest(UpdateStudyResourceRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            description: $request->validated('description'),
            subjectId: (int) $request->validated('subject_id'),
            gradeLevelId: (int) $request->validated('grade_level_id'),
            classroomId: $request->validated('classroom_id') ? (int) $request->validated('classroom_id') : null,
            teacherId: (int) $request->validated('teacher_id'),
            type: $request->validated('type'),
            externalUrl: $request->validated('external_url') ?: null,
            file: $request->file('file'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'subject_id' => $this->subjectId,
            'grade_level_id' => $this->gradeLevelId,
            'classroom_id' => $this->classroomId,
            'teacher_id' => $this->teacherId,
            'type' => $this->type,
            'external_url' => $this->externalUrl,
        ];
    }
}
