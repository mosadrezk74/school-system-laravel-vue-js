<?php

namespace Modules\Portal\Http\Controllers;

use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Actions\BuildParentDashboardAction;
use Modules\Portal\DTOs\PortalIdentityDTO;
use Modules\Portal\Services\PortalScopeService;

class ParentDashboardController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(BuildParentDashboardAction $action): Response
    {
        return $this->render('Portal::Parent/Dashboard', [
            'identity' => PortalIdentityDTO::fromScope($this->scope)->toArray(),
            'widgets' => $action->execute(),
            'hasChildren' => $this->scope->hasChildren(),
        ]);
    }
}
