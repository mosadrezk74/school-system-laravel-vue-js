<?php

namespace Modules\Subject\DTOs;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseDTO;

final class CreateSubjectDTO extends BaseDTO
{
    /**
     * @param  array<string, string|null>  $name
     * @param  array<string, string|null>|null  $description
     */
    public function __construct(
        public array $name,
        public ?array $description,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}
