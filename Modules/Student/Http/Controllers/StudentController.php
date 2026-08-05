<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Student\Actions\CreateStudentAction;
use Modules\Student\Actions\DeleteStudentAction;
use Modules\Student\Actions\UpdateStudentAction;
use Modules\Student\DTOs\StudentData;
use Modules\Student\Http\Requests\StoreStudentRequest;
use Modules\Student\Http\Requests\UpdateStudentRequest;
use Modules\Student\Repositories\StudentRepository;
use Modules\Student\Transformers\StudentResource;

class StudentController extends Controller
{
    public function __construct(
        protected StudentRepository $studentRepository
    ) {}

    public function index(): Response
    {
        $students = $this->studentRepository->getPaginated();

        return Inertia::render('Student::Index', [
            'rows' => StudentResource::collection($students)->response()->getData(true),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Student::Create');
    }

    public function store(StoreStudentRequest $request, CreateStudentAction $action): RedirectResponse
    {
        $data = StudentData::fromStoreRequest($request);
        $action->execute($data);

        return redirect()->route('students.index');
    }

    public function edit(int $id): Response
    {
        $student = $this->studentRepository->findWithProfile($id);

        return Inertia::render('Student::Edit', [
            'student' => new StudentResource($student),
        ]);
    }

    public function update(UpdateStudentRequest $request, int $id, UpdateStudentAction $action): RedirectResponse
    {
        $data = StudentData::fromUpdateRequest($request);
        $action->execute($id, $data);

        return redirect()->route('students.index');
    }

    public function destroy(int $id, DeleteStudentAction $action): RedirectResponse
    {
        $action->execute($id);

        return redirect()->route('students.index');
    }
}
