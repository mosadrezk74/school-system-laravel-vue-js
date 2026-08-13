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
     *
     * The parent call keeps BaseRequest's string normalisation: overriding
     * this hook without it would silently exempt every portal form from the
     * trimming the admin forms get.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'teacher_id' => app(PortalScopeService::class)->teacherUserId(),
        ]);
    }
}
