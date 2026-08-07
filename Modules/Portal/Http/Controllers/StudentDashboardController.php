<?php

namespace Modules\Portal\Http\Controllers;

use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Actions\BuildStudentDashboardAction;
use Modules\Portal\DTOs\PortalIdentityDTO;
use Modules\Portal\Services\PortalScopeService;

class StudentDashboardController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(BuildStudentDashboardAction $action): Response
    {
        return $this->render('Portal::Student/Dashboard', [
            'identity' => PortalIdentityDTO::fromScope($this->scope)->toArray(),
            'widgets' => $action->execute(),
        ]);
    }
}
