<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Setting\Services\LanguageService;

class UpdateProfileRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($userId)],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'language' => ['required', 'string', Rule::in(app(LanguageService::class)->activeLocales())],
            'theme' => ['required', 'string', 'in:light,dark'],
            'timezone' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('auth::account.fields.name'),
            'email' => __('auth::account.fields.email'),
            'username' => __('auth::account.fields.username'),
            'phone' => __('auth::account.fields.phone'),
            'gender' => __('auth::account.fields.gender'),
            'birth_date' => __('auth::account.fields.birth_date'),
            'national_id' => __('auth::account.fields.national_id'),
            'language' => __('auth::account.fields.language'),
            'theme' => __('auth::account.fields.theme'),
            'timezone' => __('auth::account.fields.timezone'),
        ];
    }
}
