<?php

namespace Modules\Auth\Repositories;

use App\Models\User;
use Modules\Auth\DTOs\UpdateProfileDTO;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function updateProfile(User $user, UpdateProfileDTO $dto): User
    {
        $user->fill([
            'name' => $dto->name,
            'email' => $dto->email,
            'username' => $dto->username,
            'phone' => $dto->phone,
            'gender' => $dto->gender,
            'birth_date' => $dto->birthDate,
            'national_id' => $dto->nationalId,
            'language' => $dto->language,
            'theme' => $dto->theme,
            'timezone' => $dto->timezone,
        ]);

        $user->save();

        return $user;
    }

    public function updatePassword(User $user, string $password): void
    {
        // The `password` cast is 'hashed' on User, so assigning the plain
        // value here and saving hashes it automatically.
        $user->password = $password;
        $user->save();
    }
}
