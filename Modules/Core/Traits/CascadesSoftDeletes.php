<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Core\Support\DeleteRestrictions;

/**
 * Automatically soft-deletes (or hard-deletes, if the related model has no
 * SoftDeletes trait) every relation listed in $cascadeDeletes when the model
 * itself is deleted.
 *
 * Restoring the parent only restores relations explicitly listed in
 * $cascadeRestores - this is deliberately separate from $cascadeDeletes so a
 * model can cascade-delete a relation without silently reviving it later, or
 * (more commonly here) leave a relation out of $cascadeDeletes entirely
 * (never touched by delete) while still needing it un-trashed if an older
 * row happens to be soft-deleted from before that relation was excluded.
 *
 * Usage: `use CascadesSoftDeletes, SoftDeletes;` and declare
 * `protected array $cascadeDeletes = ['relationName', ...];` and/or
 * `protected array $cascadeRestores = ['relationName', ...];` where each
 * entry is the name of a HasMany/HasOne relation method on the model.
 *
 * Requires the model to also use Illuminate\Database\Eloquent\SoftDeletes
 * (so isForceDeleting() is available to skip cascading on a real forceDelete()).
 */
trait CascadesSoftDeletes
{
    public static function bootCascadesSoftDeletes(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            DeleteRestrictions::without(function () use ($model) {
                foreach ($model->cascadeDeletes ?? [] as $relation) {
                    $related = $model->{$relation}();

                    $related instanceof HasOne
                        ? $related->first()?->delete()
                        : $related->get()->each->delete();
                }
            });
        });

        static::restored(function ($model) {
            foreach ($model->cascadeRestores ?? [] as $relation) {
                $related = $model->{$relation}();

                $related instanceof HasOne
                    ? $related->withTrashed()->first()?->restore()
                    : $related->withTrashed()->get()->each->restore();
            }
        });
    }
}
