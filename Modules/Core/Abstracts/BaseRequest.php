<?php

namespace Modules\Core\Abstracts;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Core\Traits\ApiResponseTrait;
use Propaganistas\LaravelPhone\PhoneNumber;

abstract class BaseRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalises phone numbers to E.164 when propaganistas/laravel-phone is
     * available. The class check is required: that package came from another
     * project and isn't installed here, so an unguarded call would fatal on
     * any request carrying a `phone` field.
     */
    protected function passedValidation(): void
    {
        if (! $this->filled('phone') || ! class_exists(PhoneNumber::class)) {
            return;
        }

        $normalized = (string) (new PhoneNumber($this->input('phone')))->formatE164();

        $this->merge(['phone' => $normalized]);

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = $this->validator;
        $validator->setValue('phone', $normalized);
    }

    public function attributes(): array
    {
        return [];
    }

    /**
     * Inertia visits are ordinary HTML requests, so errors have to go back
     * through the session for useForm() to expose them as form.errors.
     * Returning JSON here (the API behaviour) would leave every Vue form
     * silently unable to show why a save failed.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                $this->validationError($validator->errors()->toArray())
            );
        }

        parent::failedValidation($validator);
    }

    protected function failedAuthorization()
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException($this->forbidden());
        }

        parent::failedAuthorization();
    }

    public function validatedWithout($keys = []): array
    {
        $data = parent::validated();
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    public function onlyFilled(): array
    {
        return array_filter($this->validated(), function ($value) {
            return ! is_null($value) && $value !== '';
        });
    }
}
