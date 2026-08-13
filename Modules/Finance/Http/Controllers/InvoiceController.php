<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Modules\Academic\Services\AcademicContextService;
use Modules\Core\Abstracts\BaseInertiaController;
use Modules\Core\Constants\Pagination;
use Modules\Core\DTOs\ListQueryDTO;
use Modules\Core\Support\Filters\FilterOptions;
use Modules\Finance\Actions\ArchiveInvoiceAction;
use Modules\Finance\Actions\CancelInvoiceAction;
use Modules\Finance\Actions\CreateInvoiceAction;
use Modules\Finance\Actions\GenerateInvoiceFromStudentFeesAction;
use Modules\Finance\Actions\RestoreInvoiceAction;
use Modules\Finance\Actions\UpdateInvoiceAction;
use Modules\Finance\DTOs\CreateInvoiceDTO;
use Modules\Finance\DTOs\UpdateInvoiceDTO;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Http\Requests\GenerateInvoiceRequest;
use Modules\Finance\Http\Requests\StoreInvoiceRequest;
use Modules\Finance\Http\Requests\UpdateInvoiceRequest;
use Modules\Finance\Models\FeeStructure;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Queries\InvoiceQuery;
use Modules\Finance\Transformers\InvoiceResource;
use Modules\Student\Models\StudentProfile;

class InvoiceController extends BaseInertiaController
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Invoice::class);

        $filters = ListQueryDTO::fromRequest($request);

        $query = (new InvoiceQuery)->academicYearId(AcademicContextService::yearId());

        $invoices = $query
            ->search($filters->search)
            ->where($filters->values)
            ->sort($filters->sortBy, $filters->sortOrder)
            ->paginate($filters->perPage);

        return $this->render('Finance::Invoices/Index', [
            'invoices' => InvoiceResource::collection($invoices),
            'filters' => [
                Pagination::SEARCH_PARAM => $filters->search,
                ...$query->activeFilters($filters->values),
            ],
            'filterDefinitions' => $query->filterSchema([
                'student_id' => $this->studentFilterOptions(),
            ]),
            ...$this->lookups(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Invoice::class);

        return $this->render('Finance::Invoices/Create', $this->lookups());
    }

    public function store(StoreInvoiceRequest $request, CreateInvoiceAction $action): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $action->execute(CreateInvoiceDTO::fromRequest($request));

        return $this->toRoute('invoices.index', [], __('finance::finance.invoice_created'));
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['student.user', 'items.feeStructure', 'payments.student.user']);

        return $this->render('Finance::Invoices/Show', [
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $this->authorize('update', $invoice);

        $invoice->load(['student.user', 'items.feeStructure']);

        return $this->render('Finance::Invoices/Edit', [
            'invoice' => new InvoiceResource($invoice),
            ...$this->lookups(),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice, UpdateInvoiceAction $action): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $action->execute(['invoice' => $invoice, 'dto' => UpdateInvoiceDTO::fromRequest($request)]);

        return $this->toRoute('invoices.index', [], __('finance::finance.invoice_updated'));
    }

    public function destroy(Invoice $invoice, ArchiveInvoiceAction $action): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $action->execute($invoice);

        return $this->toRoute('invoices.index', [], __('finance::finance.invoice_archived'));
    }

    public function restore(int $invoice, RestoreInvoiceAction $action): RedirectResponse
    {
        $invoice = Invoice::withTrashed()->findOrFail($invoice);

        $this->authorize('restore', $invoice);

        $action->execute($invoice);

        return $this->toRoute('invoices.index', [], __('finance::finance.invoice_restored'));
    }

    public function cancel(Invoice $invoice, CancelInvoiceAction $action): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        $action->execute($invoice);

        return $this->backWithSuccess(__('finance::finance.invoice_cancelled'));
    }

    public function generate(GenerateInvoiceRequest $request, GenerateInvoiceFromStudentFeesAction $action): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $action->execute([
            'studentId' => (int) $request->validated('student_id'),
            'issueDate' => $request->validated('issue_date'),
            'dueDate' => $request->validated('due_date'),
            'notes' => $request->validated('notes'),
        ]);

        return $this->toRoute('invoices.index', [], __('finance::finance.invoice_generated'));
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return [
            'students' => StudentProfile::query()
                ->where('academic_year_id', AcademicContextService::yearId())
                ->where('is_active', true)
                ->with('user:id,name')
                ->get(['id', 'user_id', 'admission_number'])
                ->map(fn (StudentProfile $student) => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'admission_number' => $student->admission_number,
                ])->values(),
            'feeStructures' => FeeStructure::query()->with('feeCategory')->get()
                ->map(fn (FeeStructure $feeStructure) => [
                    'id' => $feeStructure->id,
                    'name' => $feeStructure->name,
                    'amount' => $feeStructure->amount,
                ])->values(),
            'statuses' => InvoiceStatus::options(),
        ];
    }

    /**
     * Students for the filter bar's picker, scoped to the active academic year
     * the same way the list itself is.
     *
     * @return array<int, array{title: string, value: mixed}>
     */
    private function studentFilterOptions(): array
    {
        return FilterOptions::from(
            StudentProfile::query()
                ->where('academic_year_id', AcademicContextService::yearId())
                ->where('is_active', true)
                ->with('user:id,name')
                ->get(),
            fn (StudentProfile $student): string => $student->user?->name ?? (string) $student->admission_number,
        );
    }
}
