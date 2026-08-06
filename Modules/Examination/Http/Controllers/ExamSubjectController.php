<?php

namespace Modules\Examination\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Examination\Actions\AddExamSubjectAction;
use Modules\Examination\Actions\RemoveExamSubjectAction;
use Modules\Examination\Actions\UpdateExamSubjectAction;
use Modules\Examination\DTOs\CreateExamSubjectDTO;
use Modules\Examination\DTOs\UpdateExamSubjectDTO;
use Modules\Examination\Http\Requests\StoreExamSubjectRequest;
use Modules\Examination\Http\Requests\UpdateExamSubjectRequest;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamSubject;

class ExamSubjectController extends BaseInertiaController
{
    public function store(StoreExamSubjectRequest $request, Exam $exam, AddExamSubjectAction $action): RedirectResponse
    {
        $this->authorize('update', $exam);

        $action->execute(['exam' => $exam, 'dto' => CreateExamSubjectDTO::fromRequest($request)]);

        return $this->backWithSuccess(__('examination::exam.subject_added'));
    }

    public function update(UpdateExamSubjectRequest $request, Exam $exam, ExamSubject $examSubject, UpdateExamSubjectAction $action): RedirectResponse
    {
        $this->authorize('update', $exam);

        $action->execute(['examSubject' => $examSubject, 'dto' => UpdateExamSubjectDTO::fromRequest($request)]);

        return $this->backWithSuccess(__('examination::exam.subject_updated'));
    }

    public function destroy(Exam $exam, ExamSubject $examSubject, RemoveExamSubjectAction $action): RedirectResponse
    {
        $this->authorize('update', $exam);

        $action->execute($examSubject);

        return $this->backWithSuccess(__('examination::exam.subject_removed'));
    }
}
