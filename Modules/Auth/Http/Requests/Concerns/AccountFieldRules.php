<?php

namespace Modules\Auth\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Modules\Auth\Enums\Gender;
use Modules\Core\Rules\PhoneNumberRule;

/**
 * Rules for the `users` columns that every account form writes: the student,
 * teacher, guardian, admin and profile screens all create or edit a row in the
 * same table, and each had grown its own copy of these rules with its own
 * limits. That drift was not cosmetic — three of them allowed a 255-character
 * username into a `varchar(100)` column, so a long name was either truncated
 * or rejected by MySQL after validation had already passed.
 *
 * Every limit here is the column's own width. Anything the domain wants
 * tighter than the column (a student's plausible age, say) stays in the
 * request that knows about it.
 */
trait AccountFieldRules
{
    /** `users.name` is varchar(255). */
    public const NAME_MAX = 255;

    /** `users.email` is varchar(255). */
    public const EMAIL_MAX = 255;

    /** `users.username` is varchar(100) — not 255, which is what three requests assumed. */
    public const USERNAME_MAX = 100;

    /** `users.phone` is varchar(20). */
    public const PHONE_MAX = 20;

    /** `users.national_id` is varchar(20). */
    public const NATIONAL_ID_MAX = 20;

    /**
     * @return array<int, mixed>
     */
    protected function nameRules(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'min:2', 'max:'.self::NAME_MAX];
    }

    /**
     * `rfc` is full RFC 5322 parsing rather than Laravel's permissive default.
     *
     * Deliberately not `dns`. That variant asks the resolver for an MX record
     * on every submission, which makes saving a form depend on the network,
     * costs a round-trip each time, and rejects perfectly deliverable
     * addresses at domains that publish only an A record — a school running
     * its own internal mail domain is exactly that case. Whether an address
     * accepts mail is a delivery question, not a validation one.
     *
     * @return array<int, mixed>
     */
    protected function emailRules(?int $ignoreUserId = null, bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'email:rfc',
            'max:'.self::EMAIL_MAX,
            Rule::unique('users', 'email')->ignore($ignoreUserId),
        ];
    }

    /**
     * The regex keeps the value URL- and login-safe; without it a username
     * containing a slash or a space breaks the routes that resolve users by it.
     *
     * @return array<int, mixed>
     */
    protected function usernameRules(?int $ignoreUserId = null, bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:3',
            'max:'.self::USERNAME_MAX,
            'regex:/^[a-zA-Z0-9._-]+$/',
            Rule::unique('users', 'username')->ignore($ignoreUserId),
        ];
    }

    /**
     * `users.phone` carries a unique index, so a duplicate is a database error
     * unless validation catches it first — which only the profile form did.
     *
     * The format itself lives in PhoneNumberRule so that what the school
     * accepts as a phone number is defined once, rather than as a regex
     * literal repeated across Requests.
     *
     * @return array<int, mixed>
     */
    protected function phoneRules(?int $ignoreUserId = null, bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:'.self::PHONE_MAX,
            new PhoneNumberRule,
            Rule::unique('users', 'phone')->ignore($ignoreUserId),
        ];
    }

    /**
     * Digits only, and unique: two people cannot share a national identity
     * number, and a partially-typed one is worse than none.
     *
     * @return array<int, mixed>
     */
    protected function nationalIdRules(?int $ignoreUserId = null): array
    {
        return [
            'nullable',
            'string',
            'regex:/^[0-9]{8,'.self::NATIONAL_ID_MAX.'}$/',
            Rule::unique('users', 'national_id')->ignore($ignoreUserId),
        ];
    }

    /**
     * A plausible date of birth for this kind of account. `before:today` on its
     * own — which is all four of these forms had — accepts a newborn teacher
     * and a date in the year 1200 alike.
     *
     * @param  int  $minAge  Youngest the person may be, in years.
     * @param  int  $maxAge  Oldest the person may be, in years.
     * @return array<int, mixed>
     */
    protected function birthDateRules(int $minAge, int $maxAge, bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'date',
            'before:'.now()->subYears($minAge)->toDateString(),
            'after:'.now()->subYears($maxAge)->toDateString(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function genderRules(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', Rule::enum(Gender::class)];
    }

    /**
     * Shared validation messages for the rules above. A request merges these
     * into its own `messages()` so the regexes explain themselves instead of
     * rendering Laravel's "format is invalid".
     *
     * @return array<string, string>
     */
    protected function accountFieldMessages(string $prefix = ''): array
    {
        return [
            $prefix.'username.regex' => __('auth::validation.username_format'),
            $prefix.'national_id.regex' => __('auth::validation.national_id_format'),
            $prefix.'birth_date.before' => __('auth::validation.birth_date_too_recent'),
            $prefix.'birth_date.after' => __('auth::validation.birth_date_too_old'),
        ];
    }
}
