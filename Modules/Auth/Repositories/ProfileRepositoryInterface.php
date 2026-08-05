<?php

namespace Modules\Auth\Repositories;

use App\Models\User;
use Modules\Auth\DTOs\UpdateProfileDTO;

interface ProfileRepositoryInterface
{
    public function updateProfile(User $user, UpdateProfileDTO $dto): User;

    public function updatePassword(User $user, string $password): void;
}
