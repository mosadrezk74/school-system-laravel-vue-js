<?php

namespace Modules\Setting\DTOs;

use Modules\Setting\Http\Requests\UpdateOrganizationSettingsRequest;

final readonly class UpdateOrganizationSettingsDTO
{
    public function __construct(
        public bool $multiBranch,
        public bool $multiAcademicYear,
    ) {}

    public static function fromRequest(UpdateOrganizationSettingsRequest $request): self
    {
        return new self(
            multiBranch: $request->boolean('multi_branch'),
            multiAcademicYear: $request->boolean('multi_academic_year'),
        );
    }
}
