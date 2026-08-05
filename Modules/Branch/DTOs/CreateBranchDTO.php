<?php

namespace Modules\Branch\DTOs;

use Illuminate\Http\Request;
use Modules\Branch\Http\Requests\StoreBranchRequest;
use Modules\Core\Abstracts\BaseDTO;

final class CreateBranchDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public ?string $code,
        public ?string $address,
        public ?string $city,
        public ?string $phone,
        public ?int $managerId,
        public bool $isMain,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            code: $request->validated('code'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            phone: $request->validated('phone'),
            managerId: $request->validated('manager_id') ? (int) $request->validated('manager_id') : null,
            isMain: (bool) $request->validated('is_main', false),
            isActive: (bool) $request->validated('is_active', true), 
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
            'manager_id' => $this->managerId,
            'is_main' => $this->isMain,
            'is_active' => $this->isActive, 
        ];
    }
}