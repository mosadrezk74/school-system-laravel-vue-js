<?php

namespace Modules\Student\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function create(array $userData, array $profileData): User;

    public function update(User $student, array $userData, array $profileData): bool;

    public function delete(User $student): bool;
}
