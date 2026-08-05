<?php

namespace Modules\Core\Traits;

use Illuminate\Validation\Validator;

trait ValidationTrait
{
    protected function validateData(array $data, array $rules, array $messages = []): array
    {
        $validator = validator($data, $rules, $messages);
        if ($validator->fails()) {
            throw new \Exception(json_encode($validator->errors()->toArray()), 422);
        }

        return $validator->validated();
    }

    protected function validateWithCustom(array $data, array $rules, array $messages = []): Validator
    {
        return validator($data, $rules, $messages);
    }

    protected function validateExists(string $model, int $id, string $field = 'id'): void
    {
        if (! $model::where($field, $id)->exists()) {
            throw new \Exception('Record not found', 404);
        }
    }

    protected function validateUnique(string $model, string $field, mixed $value, ?int $excludeId = null): void
    {
        $query = $model::where($field, $value);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        if ($query->exists()) {
            throw new \Exception('Value already exists', 409);
        }
    }

    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validatePhone(string $phone): bool
    {
        return preg_match('/^[0-9+\-\s()]{10,15}$/', $phone);
    }

    protected function validatePassword(string $password): bool
    {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }
}
