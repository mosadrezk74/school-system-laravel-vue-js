<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Setting\Actions\CreateSchoolTermAction;
use Modules\Setting\Actions\DeleteSchoolTermAction;
use Modules\Setting\Actions\PublishSchoolTermAction;
use Modules\Setting\Actions\UpdateSchoolTermAction;
use Modules\Setting\DTOs\CreateSchoolTermDTO;
use Modules\Setting\DTOs\SchoolTermFilterDTO;
use Modules\Setting\DTOs\UpdateSchoolTermDTO;
use Modules\Setting\Enums\SchoolTermStatus;
use Modules\Setting\Http\Requests\StoreSchoolTermRequest;
use Modules\Setting\Http\Requests\UpdateSchoolTermRequest;
use Modules\Setting\Models\SchoolTerm;
use Modules\Setting\Queries\SchoolTermQuery;
use Modules\Setting\Transformers\SchoolTermResource;

class SchoolTermController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SchoolTerm::class);

        $filters = SchoolTermFilterDTO::fromRequest($request);

        $terms = (new SchoolTermQuery)
            ->withAuthor()
            ->withAcceptanceCount()
            ->search($filters->search)
            ->status($filters->status)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Setting::SchoolTerms/Index', [
            'terms' => SchoolTermResource::collection($terms),
            'filters' => $request->only([Pagination::SEARCH_PARAM, 'status']),
            'statusOptions' => SchoolTermStatus::options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SchoolTerm::class);

        /*
         * The version in force is offered as a starting point so drafting a
         * revision means editing the current wording rather than retyping it.
         */
        $current = SchoolTerm::query()->published()->first();

        return $this->render('Setting::SchoolTerms/Create', [
            'currentTerm' => $current ? new SchoolTermResource($current) : null,
        ]);
    }

    public function store(StoreSchoolTermRequest $request, CreateSchoolTermAction $action): RedirectResponse
    {
        $this->authorize('create', SchoolTerm::class);

        $action->execute(CreateSchoolTermDTO::fromRequest($request));

        return $this->toRoute('settings.school-terms.index', [], __('setting::school_terms.created'));
    }

    public function show(SchoolTerm $schoolTerm): Response
    {
        $this->authorize('view', $schoolTerm);

        return $this->render('Setting::SchoolTerms/Show', [
            'term' => new SchoolTermResource($schoolTerm->loadCount('acceptances')->load('createdBy')),
        ]);
    }

    public function edit(SchoolTerm $schoolTerm): Response
    {
        $this->authorize('update', $schoolTerm);

        return $this->render('Setting::SchoolTerms/Edit', [
            'term' => new SchoolTermResource($schoolTerm),
        ]);
    }

    public function update(
        UpdateSchoolTermRequest $request,
        SchoolTerm $schoolTerm,
        UpdateSchoolTermAction $action,
    ): RedirectResponse {
        $this->authorize('update', $schoolTerm);

        $action->execute([
            'term' => $schoolTerm,
            'dto' => UpdateSchoolTermDTO::fromRequest($request),
        ]);

        return $this->toRoute('settings.school-terms.index', [], __('setting::school_terms.updated'));
    }

    public function publish(SchoolTerm $schoolTerm, PublishSchoolTermAction $action): RedirectResponse
    {
        $this->authorize('publish', $schoolTerm);

        $action->execute($schoolTerm);

        return $this->toRoute('settings.school-terms.index', [], __('setting::school_terms.published'));
    }

    public function destroy(SchoolTerm $schoolTerm, DeleteSchoolTermAction $action): RedirectResponse
    {
        $this->authorize('delete', $schoolTerm);

        $action->execute($schoolTerm);

        return $this->toRoute('settings.school-terms.index', [], __('setting::school_terms.deleted'));
    }
}
