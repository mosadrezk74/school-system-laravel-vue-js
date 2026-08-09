<?php

namespace Modules\Academic\DTOs;

use Modules\Core\Abstracts\BaseDTO;

class AcademicYearDTO extends BaseDTO
{
    /*
     * Declared explicitly rather than left to BaseDTO's magic __set: assigning
     * undeclared properties is deprecated in PHP 8.2+, and declaring them also
     * documents the DTO's shape.
     */
    public ?string $name = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public bool $is_current = false;

    public bool $is_active = true;

    /** @var array<int, string> */
    protected static array $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_active',
    ];

    /** @var array<string, string> */
    protected static array $casts = [
        'is_current' => 'bool',
        'is_active' => 'bool',
    ];

    /** Fields actually present in the incoming payload. */
    protected array $supplied = [];

    public function fill(array $data): self
    {
        $this->supplied = array_keys(array_intersect_key($data, array_flip(static::$fillable)));

        return parent::fill($data);
    }

    /**
     * Attributes for a create: declared defaults are included, so is_active
     * and is_current are never written as NULL when the payload omits them.
     *
     * @return array<string, mixed>
     */
    public function toCreateArray(): array
    {
        return [
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_current' => $this->is_current,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Attributes for an update: only what the caller actually sent, so a PATCH
     * of one field can't blank the others back to their defaults.
     *
     * @return array<string, mixed>
     */
    public function toUpdateArray(): array
    {
        return array_intersect_key(
            [
                'name' => $this->name,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'is_current' => $this->is_current,
                'is_active' => $this->is_active,
            ],
            array_flip($this->supplied),
        );
    }
}
