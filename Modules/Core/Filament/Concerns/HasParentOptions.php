<?php

namespace Modules\Core\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasParentOptions
{
    /**
     * Ids of the record and every descendant beneath it. Selecting any of
     * them as a parent would create a cycle in the tree.
     *
     * @return array<int, int|string>
     */
    protected static function descendantIds(Model $record, string $childrenRelation = 'children'): array
    {
        $ids = [$record->getKey()];

        foreach ($record->{$childrenRelation} as $child) {
            $ids = array_merge($ids, static::descendantIds($child, $childrenRelation));
        }

        return $ids;
    }
}
