<?php

namespace Modules\Portal\Traits;

use Modules\Portal\Services\PortalScopeService;

trait ForcesStudentOwnership
{
    /**
     * The admin forms let staff record a submission on behalf of any student,
     * so student_id arrives from the client. Inside the portal that field is
     * not the student's to choose: it is overwritten with the signed-in
     * student before validation runs, so the reused DTOs and Actions receive
     * an ownership value the request could never influence.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_id' => app(PortalScopeService::class)->currentStudentProfile()?->id,
        ]);
    }
}
