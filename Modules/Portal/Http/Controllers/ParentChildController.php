<?php

namespace Modules\Portal\Http\Controllers;

use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Portal\Services\PortalScopeService;
use Modules\Student\Models\StudentProfile;

class ParentChildController extends BaseInertiaController
{
    public function __construct(private readonly PortalScopeService $scope) {}

    public function index(): Response
    {
        return $this->render('Portal::Parent/Children', [
            'children' => $this->scope->guardianChildren()
                ->map(fn (StudentProfile $child): array => [
                    'id' => $child->id,
                    'name' => $child->user?->name,
                    'admission_number' => $child->admission_number,
                    'class_name' => $child->currentClass?->name,
                    'is_active' => (bool) $child->is_active,
                ])
                ->values(),
        ]);
    }
}
