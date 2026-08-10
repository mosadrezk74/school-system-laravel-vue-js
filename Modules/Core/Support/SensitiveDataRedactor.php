<?php

namespace Modules\Core\Support;

/**
 * Strips credentials and payment details out of anything on its way into an
 * error record.
 *
 * This application handles fees, invoices and payments, so a naive
 * `$request->all()` in a log is a real breach risk. Matching is done on a
 * denylist of key *fragments* and is case-insensitive, so `card_number`,
 * `cardNumber` and `payment.card_number` are all caught by `card`.
 */
final class SensitiveDataRedactor
{
    public const REDACTED = '[REDACTED]';

    /**
     * Key fragments that must never reach storage.
     *
     * @var array<int, string>
     */
    private const DENYLIST = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'apikey',
        'secret',
        'authorization',
        'auth',
        'cookie',
        'session',
        'csrf',
        'xsrf',
        'credentials',
        'card',
        'card_number',
        'cardnumber',
        'cvv',
        'cvc',
        'iban',
        'swift',
        'bank',
        'account_number',
        'routing',
        'pin',
        'otp',
        'private_key',
        'signature',
        'remember_token',
    ];

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public function redact(array $data, int $depth = 0): array
    {
        // Guards against deeply nested or self-referential payloads.
        if ($depth > 8) {
            return [self::REDACTED];
        }

        $clean = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && $this->isSensitive($key)) {
                $clean[$key] = self::REDACTED;

                continue;
            }

            $clean[$key] = is_array($value)
                ? $this->redact($value, $depth + 1)
                : $this->redactScalar($value);
        }

        return $clean;
    }

    public function isSensitive(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::DENYLIST as $denied) {
            if (str_contains($normalized, $denied)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Objects and resources are reduced to their type: they can hold
     * credentials in properties that a key-based denylist would never see.
     */
    private function redactScalar(mixed $value): mixed
    {
        if (is_scalar($value) || $value === null) {
            return $value;
        }

        return is_object($value) ? '['.get_class($value).']' : self::REDACTED;
    }
}
