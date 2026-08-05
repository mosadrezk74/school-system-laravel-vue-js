<?php

namespace Modules\Core\Abstracts;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Laravel wraps a single resource in a "data" key by default, which would
     * make an Inertia prop arrive as { data: {...} } and every page reach
     * through `prop.data.name`. Collections keep their own pagination
     * envelope; only this extra nesting is removed.
     */
    public static $wrap = null;

    protected function getIncludes(): array
    {
        $includes = request()->input('include', '');

        return $includes ? explode(',', $includes) : [];
    }

    protected function getFields(): array
    {
        $fields = request()->input('fields', '');

        return $fields ? explode(',', $fields) : [];
    }

    protected function includeIfLoaded(string $relation): mixed
    {
        if ($this->resource->relationLoaded($relation)) {
            return $this->whenLoaded($relation);
        }

        return null;
    }

    protected function fields(array $data): array
    {
        $fields = $this->getFields();
        if (empty($fields)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($fields));
    }
}
