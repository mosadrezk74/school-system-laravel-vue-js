<?php

namespace Modules\Core\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Constants\Pagination;

abstract class BaseModel extends Model
{
    /**
     * Every table in the schema carries deleted_at, and school records
     * (students, invoices, results) must never be destroyed outright.
     */
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $perPage = Pagination::DEFAULT_PER_PAGE;

    public function getCacheKey(): string
    {
        return class_basename($this) . ':' . $this->id;
    }

    public function touchCache(): void
    {
        // Override in child if needed
    }

    public function toArray($includes = [])
    {
        $data = parent::toArray();
        foreach ($includes as $include) {
            if ($this->relationLoaded($include)) {
                $data[$include] = $this->$include->toArray();
            }
        }
        return $data;
    }
}
