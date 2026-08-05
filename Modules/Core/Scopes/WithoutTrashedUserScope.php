<?php

namespace Modules\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;

class WithoutTrashedUserScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $users = (new User)->getTable();
        $table = $model->getTable();

        foreach ($model->userColumns ?? ['user_id'] as $column) {
            $builder->whereNot(
                fn (Builder $query) => $query->whereExists(
                    fn ($sub) => $sub->select(DB::raw(1))
                        ->from($users)
                        ->whereColumn($users.'.id', $table.'.'.$column)
                        ->whereNotNull($users.'.deleted_at'),
                ),
            );
        }
    }
}
