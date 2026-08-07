<?php

namespace Modules\Report\DTOs;

use Illuminate\Http\Request;

final readonly class ReportFilterDTO
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function __construct(
        public array $values,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self($request->input('filters', []));
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->values[$key] ?? null;

        return $value === '' || $value === null ? $default : $value;
    }

    public function int(string $key): ?int
    {
        $value = $this->get($key);

        return $value === null ? null : (int) $value;
    }

    public function string(string $key): ?string
    {
        $value = $this->get($key);

        return $value === null ? null : (string) $value;
    }

    public function boolean(string $key): ?bool
    {
        $value = $this->get($key);

        return $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOL);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
