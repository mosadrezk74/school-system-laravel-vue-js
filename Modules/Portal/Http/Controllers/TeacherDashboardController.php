<?php

namespace Modules\Portal\Http\Controllers;

use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Actions\BuildTeacherDashboardAction;
use Modules\Portal\DTOs\PortalIdentityDTO;
use Modules\Portal\Services\PortalScopeService;

class TeacherDashboardController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(BuildTeacherDashboardAction $action): Response
    {
        return $this->render('Portal::Teacher/Dashboard', [
            'identity' => PortalIdentityDTO::fromScope($this->scope)->toArray(),
            'widgets' => $action->execute(),
        ]);
    }
}
