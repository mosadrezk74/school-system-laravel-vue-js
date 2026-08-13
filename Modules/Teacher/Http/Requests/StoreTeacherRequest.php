<?php

namespace Modules\Teacher\Http\Requests;

use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Teacher\Http\Requests\Concerns\TeacherProfileRules;

class StoreTeacherRequest extends BaseRequest
{
    use AccountFieldRules;
    use TeacherProfileRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /*
             * Account. Limits come from AccountFieldRules so they match the
             * `users` columns — the username rule here used to allow 255
             * characters into a varchar(100).
             */
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'username' => $this->usernameRules(),
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'gender' => $this->genderRules(),
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE),
            'national_id' => $this->nationalIdRules(),
            'phone' => $this->phoneRules(),
            'is_active' => ['sometimes', 'boolean'],

            'employee_id' => $this->employeeIdRules(),

            ...$this->teacherProfileRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('teacher::teacher.fields.name'),
            'email' => __('teacher::teacher.fields.email'),
            'username' => __('teacher::teacher.fields.username'),
            'password' => __('teacher::teacher.fields.password'),
            'gender' => __('teacher::teacher.fields.gender'),
            'employee_id' => __('teacher::teacher.fields.employee_id'),
            'subject_ids' => __('teacher::teacher.fields.subject_ids'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->teacherMessages();
    }
}
