<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Setting\Actions\AcceptSchoolTermAction;
use Modules\Setting\Models\SchoolTerm;
use Modules\Setting\Services\SchoolTermService;
use Modules\Setting\Transformers\SchoolTermResource;

/**
 * The terms screen every non-admin sees.
 *
 * One controller serves students, guardians and teachers alike: the terms are
 * school-wide, so there is no per-audience scoping to justify three copies.
 * The signed-in user is the only subject involved, which is also why nothing
 * here accepts a user id or a term id from the request.
 */
class SchoolTermPortalController extends BaseInertiaController
{
    public function __construct(private readonly SchoolTermService $terms) {}

    public function show(Request $request): Response
    {
        $this->authorize('viewPublished', SchoolTerm::class);

        $status = $this->terms->statusFor($request->user());

        return $this->render('Setting::SchoolTerms/Portal', [
            'term' => $status['term'] ? new SchoolTermResource($status['term']) : null,
            'acceptedCurrent' => $status['accepted_current'],
            'acceptedAt' => $status['accepted_at'],
        ]);
    }

    /**
     * Which version is being accepted is resolved server-side from what is
     * published — the checkbox in the UI is a prompt, not the decision.
     */
    public function accept(Request $request, AcceptSchoolTermAction $action): RedirectResponse
    {
        $this->authorize('accept', SchoolTerm::class);

        $action->execute($request->user());

        return $this->backWithSuccess(__('setting::school_terms.accepted'));
    }
}
