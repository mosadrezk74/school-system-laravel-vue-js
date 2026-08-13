<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Auth\Enums\Theme;
use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Setting\Services\LanguageService;

class UpdateProfileRequest extends BaseRequest
{
    use AccountFieldRules;

    /**
     * Anyone with a login edits their own profile here — student, guardian and
     * staff alike — so the age range has to be the widest of them.
     */
    private const MIN_AGE = 2;

    private const MAX_AGE = 120;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'username' => $this->usernameRules($userId),
            'phone' => $this->phoneRules($userId),
            'gender' => $this->genderRules(required: false),
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE),
            'national_id' => $this->nationalIdRules($userId),
            'language' => ['required', 'string', Rule::in(app(LanguageService::class)->activeLocales())],
            'theme' => ['required', 'string', Rule::enum(Theme::class)],

            /*
             * `timezone` validates against PHP's own identifier list. The
             * previous `max:50` accepted "Mars/Olympus", which then made every
             * date this user sees fall back to the app default with no
             * indication that the setting had not taken.
             */
            'timezone' => ['required', 'string', 'timezone'],
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->accountFieldMessages();
    }
}
