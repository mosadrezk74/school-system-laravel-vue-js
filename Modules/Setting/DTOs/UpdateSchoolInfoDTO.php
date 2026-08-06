<?php

namespace Modules\Setting\DTOs;

use Illuminate\Http\UploadedFile;
use Modules\Setting\Http\Requests\UpdateSchoolInfoRequest;

final readonly class UpdateSchoolInfoDTO
{
    public function __construct(
        public string $schoolName,
        public ?string $schoolCode,
        public ?string $timezone,
        public ?string $currency,
        public ?string $currencySymbol,
        public ?UploadedFile $logo,
    ) {}

    public static function fromRequest(UpdateSchoolInfoRequest $request): self
    {
        return new self(
            schoolName: $request->validated('school_name'),
            schoolCode: $request->validated('school_code'),
            timezone: $request->validated('timezone'),
            currency: $request->validated('currency'),
            currencySymbol: $request->validated('currency_symbol'),
            logo: $request->file('logo'),
        );
    }
}
