<?php

namespace Modules\Portal\Traits;

use Modules\Portal\Services\PortalScopeService;

trait ForcesTeacherOwnership
{
    /**
     * The admin forms let a manager assign work to any teacher, so teacher_id
     * arrives from the client. Inside the portal that field is not the
     * teacher's to choose: it is overwritten with the signed-in user before
     * validation runs, so the reused DTOs and Actions receive an ownership
     * value the request could never influence.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'teacher_id' => app(PortalScopeService::class)->teacherUserId(),
        ]);
    }
}
