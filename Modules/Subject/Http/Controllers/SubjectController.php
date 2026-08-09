<?php

namespace Modules\Subject\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Subject\Actions\ArchiveSubjectAction;
use Modules\Subject\Actions\CreateSubjectAction;
use Modules\Subject\Actions\RestoreSubjectAction;
use Modules\Subject\Actions\UpdateSubjectAction;
use Modules\Subject\DTOs\CreateSubjectDTO;
use Modules\Subject\DTOs\SubjectFilterDTO;
use Modules\Subject\DTOs\UpdateSubjectDTO;
use Modules\Subject\Http\Requests\StoreSubjectRequest;
use Modules\Subject\Http\Requests\UpdateSubjectRequest;
use Modules\Subject\Models\Subject;
use Modules\Subject\Queries\SubjectQuery;
use Modules\Subject\Transformers\SubjectResource;

class SubjectController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Subject::class);

        $filters = SubjectFilterDTO::fromRequest($request);

        $query = (new SubjectQuery)
            ->search($filters->search)
            ->isActive($filters->isActive)
            ->sort($filters->sortBy, $filters->sortOrder);

        $subjects = $query->paginate($filters->perPage);

        return Inertia::render('Subject::Subjects/Index', [
            'subjects' => SubjectResource::collection($subjects),
            'filters' => $request->only([
                Pagination::SEARCH_PARAM,
                'is_active',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Subject::class);

        return Inertia::render('Subject::Subjects/Create');
    }

    public function store(StoreSubjectRequest $request, CreateSubjectAction $action): RedirectResponse
    {
        $this->authorize('create', Subject::class);

        $action->execute(CreateSubjectDTO::fromRequest($request));

        return $this->toRoute('subjects.index', [], __('subject::subject.created'));
    }

    public function show(Subject $subject): Response
    {
        $this->authorize('view', $subject);

        return Inertia::render('Subject::Subjects/Show', [
            'subject' => new SubjectResource($subject),
        ]);
    }

    public function edit(Subject $subject): Response
    {
        $this->authorize('update', $subject);

        return Inertia::render('Subject::Subjects/Edit', [
            'subject' => new SubjectResource($subject),
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject, UpdateSubjectAction $action): RedirectResponse
    {
        $this->authorize('update', $subject);

        $action->execute([
            'subject' => $subject,
            'dto' => UpdateSubjectDTO::fromRequest($request),
        ]);

        return $this->toRoute('subjects.index', [], __('subject::subject.updated'));
    }

    public function destroy(Subject $subject, ArchiveSubjectAction $action): RedirectResponse
    {
        $this->authorize('delete', $subject);

        $action->execute($subject);

        return $this->toRoute('subjects.index', [], __('subject::subject.archived'));
    }

    public function restore(int $subject, RestoreSubjectAction $action): RedirectResponse
    {
        $subject = Subject::withTrashed()->findOrFail($subject);

        $this->authorize('restore', $subject);

        $action->execute($subject);

        return $this->toRoute('subjects.index', [], __('subject::subject.restored'));
    }
}
