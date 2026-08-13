<?php

namespace Modules\Teacher\Http\Requests;

use Modules\Auth\Http\Requests\Concerns\AccountFieldRules;
use Modules\Core\Abstracts\BaseRequest;
use Modules\Teacher\Http\Requests\Concerns\TeacherProfileRules;
use Modules\Teacher\Models\TeacherProfile;

class UpdateTeacherRequest extends BaseRequest
{
    use AccountFieldRules;
    use TeacherProfileRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var TeacherProfile|null $teacher */
        $teacher = $this->route('teacher');
        $userId = $teacher?->user_id;

        return [
            /*
             * Same shape as the store request, with this teacher's own rows
             * excluded from the unique checks. Password is optional here:
             * blank means "keep the current one".
             */
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'username' => $this->usernameRules($userId),
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'gender' => $this->genderRules(),
            'birth_date' => $this->birthDateRules(self::MIN_AGE, self::MAX_AGE),
            'national_id' => $this->nationalIdRules($userId),
            'phone' => $this->phoneRules($userId),
            'is_active' => ['sometimes', 'boolean'],

            'employee_id' => $this->employeeIdRules($teacher?->id),

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
