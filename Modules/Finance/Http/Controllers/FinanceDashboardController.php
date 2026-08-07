<?php

namespace Modules\Finance\Http\Controllers;

use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Finance\Actions\GetFinanceDashboardAction;
use Modules\Finance\Models\Invoice;

class FinanceDashboardController extends BaseInertiaController
{
    public function index(GetFinanceDashboardAction $action): Response
    {
        $this->authorize('viewAny', Invoice::class);

        return $this->render('Finance::Dashboard/Index', [
            'statistics' => $action->execute(),
        ]);
    }
}
