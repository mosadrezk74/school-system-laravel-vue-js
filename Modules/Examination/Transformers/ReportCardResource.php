<?php

namespace Modules\Examination\Transformers;

use Illuminate\Http\Request;
use Modules\Core\Abstracts\BaseResource;
use Modules\Examination\DTOs\ReportCardDTO;

/**
 * @mixin ReportCardDTO
 */
class ReportCardResource extends BaseResource
{
    /**
     * The DTO already holds exactly the shape the page renders, so this only
     * hands it over — no figures are computed here.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ReportCardDTO $card */
        $card = $this->resource;

        return $card->toArray();
    }
}
