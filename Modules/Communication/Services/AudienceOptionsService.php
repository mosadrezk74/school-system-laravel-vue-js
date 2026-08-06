<?php

namespace Modules\Communication\Services;

use App\Models\User;
use Modules\Academic\Models\AcademicYear;
use Modules\Auth\Enums\UserRole;
use Modules\SchoolClass\Models\SchoolClass;

class AudienceOptionsService
{
    /**
     * @return array<string, mixed>
     */
    public function forForm(): array
    {
        return [
            'roles' => collect(UserRole::cases())->map(fn (UserRole $role) => [
                'title' => $role->label(),
                'value' => $role->value,
            ])->values(),
            'academicYears' => AcademicYear::query()->where('is_active', true)->get(['id', 'name']),
            'classes' => SchoolClass::query()->where('is_active', true)->get(['id', 'name', 'section_code']),
            'users' => User::query()->get(['id', 'name']),
        ];
    }
}
