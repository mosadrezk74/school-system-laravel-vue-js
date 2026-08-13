<?php

namespace Modules\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The one definition of what this application accepts as a phone number.
 *
 * The format was previously spelled as the same regex literal in several Form
 * Requests, which is how the account phone and the next-of-kin phones ended up
 * drifting apart. Changing what the school accepts is now a single edit here.
 *
 * The accepted shape is deliberately unchanged from what the app already
 * accepted: an optional leading `+`, then digits with optional spaces and
 * hyphens as grouping. It is international rather than tied to one country —
 * there is no phone/country configuration anywhere in the project to reuse,
 * and `propaganistas/laravel-phone`, which BaseRequest still guards against,
 * is not installed.
 *
 * `MAX_LENGTH` matches `users.phone`, a varchar(20). It is enforced here as
 * well as by the `max:` rule alongside it so that a caller who forgets the
 * `max:` cannot store a truncated number.
 */
class PhoneNumberRule implements ValidationRule
{
    /** `users.phone` and the `*_phone` columns on `student_profiles` are all varchar(20). */
    public const MAX_LENGTH = 20;

    /** Shortest string that can still be a real number, ignoring grouping characters. */
    public const MIN_DIGITS = 7;

    /**
     * Digits, optionally preceded by `+`, optionally grouped with spaces or
     * hyphens. Anchored, so a value cannot smuggle anything past the ends.
     */
    private const PATTERN = '/^\+?[0-9](?:[0-9\s-]*[0-9])?$/';

    /**
     * `core::validation.phone` already existed and is also used by the Filament
     * concern, so it is reused verbatim here rather than replaced — one message
     * for "this is not a phone number", however it failed.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('core::validation.phone')->translate();

            return;
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            $fail('core::validation.phone_max')->translate(['max' => self::MAX_LENGTH]);

            return;
        }

        if (! preg_match(self::PATTERN, $value)) {
            $fail('core::validation.phone')->translate();

            return;
        }

        // Grouping characters are allowed but do not count towards the length:
        // "1-2-3-4-5-6-7" is not a phone number.
        if (mb_strlen((string) preg_replace('/\D/', '', $value)) < self::MIN_DIGITS) {
            $fail('core::validation.phone_min_digits')->translate(['min' => self::MIN_DIGITS]);
        }
    }
}
