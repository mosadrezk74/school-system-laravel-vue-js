<?php

namespace Modules\Core\Traits;

use Illuminate\Support\Str;
use Modules\Core\Exceptions\RestrictedDeleteException;
use Modules\Core\Support\DeleteRestrictions;

trait RestrictsDeleteWithRelations
{
    public static function bootRestrictsDeleteWithRelations(): void
    {
        static::deleting(function ($model) {
            if (DeleteRestrictions::suspended()) {
                return;
            }

            $blocking = $model->blockingRelations();

            if ($blocking !== []) {
                throw new RestrictedDeleteException($model::class, $blocking);
            }
        });
    }

    public function blockingRelations(): array
    {
        $blocking = [];

        foreach ($this->restrictDeletes ?? [] as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            if ($this->{$relation}()->exists()) {
                $blocking[] = $this->relationLabel($relation);
            }
        }

        return $blocking;
    }

    public function canBeDeleted(): bool
    {
        return $this->blockingRelations() === [];
    }

    protected function relationLabel(string $relation): string
    {
        $key = 'base.relations.'.Str::snake($relation);

        return __($key) === $key ? Str::headline($relation) : __($key);
    }
}
