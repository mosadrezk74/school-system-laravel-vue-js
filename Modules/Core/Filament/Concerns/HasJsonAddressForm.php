<?php

namespace Modules\Core\Filament\Concerns;

trait HasJsonAddressForm
{
    /**
     * User::address is stored as a per-locale JSON object but its accessor
     * returns only the active locale's string, and its mutator json_encode()s
     * whatever it receives. Feeding the accessor's value straight back into a
     * form therefore collapses the object into a bare JSON string and both
     * locales resolve to null.
     *
     * These two hooks read and write the raw column so the ar/en inputs stay
     * faithful to what the API stores.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function fillJsonAddress(array $data): array
    {
        $raw = $this->getRecord()?->getRawOriginal('address');

        $data['address'] = $raw ? (json_decode($raw, true) ?: []) : [];

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeJsonAddress(array $data): array
    {
        $address = array_filter($data['address'] ?? [], fn ($value) => filled($value));

        $data['address'] = $address === [] ? null : $address;

        return $data;
    }
}
