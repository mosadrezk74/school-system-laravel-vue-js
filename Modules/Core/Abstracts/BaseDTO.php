<?php

namespace Modules\Core\Abstracts;

use Illuminate\Http\Request;

abstract class BaseDTO
{
    protected static array $fillable = [];
    protected static array $casts = [];

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data): self
    {
        foreach (static::$fillable as $field) {
            if (array_key_exists($field, $data)) {
                $this->{$field} = $this->cast($field, $data[$field]);
            }
        }
        return $this;
    }

    public static function fromRequest(Request $request): self
    {
        return new static($request->validated());
    }

    public static function fromArray(array $data): self
    {
        return new static($data);
    }

    public function toArray(): array
    {
        $data = [];
        foreach (static::$fillable as $field) {
            if (isset($this->{$field})) {
                $data[$field] = $this->{$field};
            }
        }
        return $data;
    }

    public function toModel(): array
    {
        return $this->toArray();
    }

    protected function cast(string $field, mixed $value): mixed
    {
        if (!isset(static::$casts[$field])) {
            return $value;
        }

        return match (static::$casts[$field]) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'bool', 'boolean' => (bool) $value,
            'string' => (string) $value,
            'array', 'json' => json_decode($value, true),
            'datetime', 'date' => \Carbon\Carbon::parse($value),
            default => $value,
        };
    }

    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        return null;
    }

    public function __isset(string $name): bool
    {
        return property_exists($this, $name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->{$name} = $value;
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'get')) {
            $field = lcfirst(substr($name, 3));
            return $this->{$field} ?? null;
        }

        if (str_starts_with($name, 'set')) {
            $field = lcfirst(substr($name, 3));
            $this->{$field} = $arguments[0] ?? null;
            return $this;
        }

        if (str_starts_with($name, 'has')) {
            $field = lcfirst(substr($name, 3));
            return isset($this->{$field});
        }

        throw new \BadMethodCallException("Method {$name} not found");
    }
}
