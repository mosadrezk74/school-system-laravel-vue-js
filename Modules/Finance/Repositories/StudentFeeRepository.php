<?php

namespace Modules\Finance\Repositories;

use Illuminate\Support\Collection;
use Modules\Finance\DTOs\CreateStudentFeeDTO;
use Modules\Finance\DTOs\UpdateStudentFeeDTO;
use Modules\Finance\Enums\StudentFeeStatus;
use Modules\Finance\Models\StudentFee;

class StudentFeeRepository implements StudentFeeRepositoryInterface
{
    public function findById(int $id): ?StudentFee
    {
        return StudentFee::query()->find($id);
    }

    public function findByIdOrFail(int $id): StudentFee
    {
        return StudentFee::query()->findOrFail($id);
    }

    public function existsForStudent(int $studentId, int $feeStructureId, ?int $excludeId = null): bool
    {
        return StudentFee::query()
            ->where('student_id', $studentId)
            ->where('fee_structure_id', $feeStructureId)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->exists();
    }

    public function create(CreateStudentFeeDTO $dto): StudentFee
    {
        return StudentFee::query()->create($dto->toArray());
    }

    /**
     * @param  array{amount: float, discount_amount: float, final_amount: float}  $amounts
     */
    public function createWithAmounts(CreateStudentFeeDTO $dto, array $amounts): StudentFee
    {
        return StudentFee::query()->create([
            ...$dto->toArray(),
            'amount' => $amounts['amount'],
            'discount_amount' => $amounts['discount_amount'],
            'final_amount' => $amounts['final_amount'],
        ]);
    }

    public function update(StudentFee $studentFee, UpdateStudentFeeDTO $dto): StudentFee
    {
        $studentFee->update($dto->toArray());

        return $studentFee->refresh();
    }

    public function delete(StudentFee $studentFee): bool
    {
        return (bool) $studentFee->delete();
    }

    public function restore(StudentFee $studentFee): bool
    {
        return (bool) $studentFee->restore();
    }

    public function lockPendingForStudent(int $studentId): Collection
    {
        return StudentFee::query()
            ->where('student_id', $studentId)
            ->where('status', StudentFeeStatus::Pending->value)
            ->lockForUpdate()
            ->get();
    }

    public function markInvoiced(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        StudentFee::query()
            ->whereIn('id', $ids)
            ->update(['status' => StudentFeeStatus::Invoiced->value]);
    }
}
