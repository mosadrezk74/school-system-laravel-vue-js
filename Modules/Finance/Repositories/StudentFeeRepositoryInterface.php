<?php

namespace Modules\Finance\Repositories;

use Illuminate\Support\Collection;
use Modules\Finance\DTOs\CreateStudentFeeDTO;
use Modules\Finance\DTOs\UpdateStudentFeeDTO;
use Modules\Finance\Models\StudentFee;

interface StudentFeeRepositoryInterface
{
    public function findById(int $id): ?StudentFee;

    public function findByIdOrFail(int $id): StudentFee;

    public function existsForStudent(int $studentId, int $feeStructureId, ?int $excludeId = null): bool;

    public function create(CreateStudentFeeDTO $dto): StudentFee;

    public function update(StudentFee $studentFee, UpdateStudentFeeDTO $dto): StudentFee;

    public function delete(StudentFee $studentFee): bool;

    public function restore(StudentFee $studentFee): bool;

    /**
     * Assigned fees that are still awaiting an invoice, locked for update so
     * two concurrent generations cannot bill the same fee twice.
     *
     * @return Collection<int, StudentFee>
     */
    public function lockPendingForStudent(int $studentId): Collection;

    /**
     * @param  array<int, int>  $ids
     */
    public function markInvoiced(array $ids): void;
}
