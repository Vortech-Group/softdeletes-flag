<?php

namespace Vortech\SoftDeletesFlag\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SoftDeletesFlagScope implements Scope
{
    protected array $extensions = ['Restore', 'RestoreOrCreate', 'CreateOrRestore', 'WithTrashed', 'WithoutTrashed', 'OnlyTrashed'];

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(
            column: $model->getQualifiedIsDeletedColumn(),
            operator: '=',
            value: false
        );
    }

    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function (Builder $builder) {
            $column = $this->getIsDeletedColumn($builder);

            return $builder->update([
                $column => true,
            ]);
        });
    }

    protected function getIsDeletedColumn(Builder $builder)
    {
        if (count((array) $builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedIsDeletedColumn();
        }

        return $builder->getModel()->getIsDeletedColumn();
    }

    protected function addRestore(Builder $builder): void
    {
        $builder->macro('restore', function (Builder $builder) {
            $builder->withTrashed();

            return $builder->update([
                $builder->getModel()->getIsDeletedColumn() => false
            ]);
        });
    }

    protected function addRestoreOrCreate(Builder $builder): void
    {
        $builder->macro('restoreOrCreate', function (Builder $builder, array $attributes = [], array $values = []) {
            $builder->withTrashed();

            return tap($builder->firstOrCreate($attributes, $values), function ($instance) {
                $instance->restore();
            });
        });
    }

    protected function addCreateOrRestore(Builder $builder): void
    {
        $builder->macro('createOrRestore', function (Builder $builder, array $attributes = [], array $values = []) {
            $builder->withTrashed();

            return tap($builder->createOrFirst($attributes, $values), function ($instance) {
                $instance->restore();
            });
        });
    }

    protected function addWithTrashed(Builder $builder): void
    {
        $builder->macro('withTrashed', function (Builder $builder, $withTrashed = true) {
            if (! $withTrashed) {
                return $builder->withoutTrashed();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    protected function addWithoutTrashed(Builder $builder): void
    {
        $builder->macro('withoutTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->where(
                column: $model->getQualifiedIsDeletedColumn(),
                operator: '=',
                value: false
            );

            return $builder;
        });
    }

    protected function addOnlyTrashed(Builder $builder): void
    {
        $builder->macro('onlyTrashed', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->where(
                column: $model->getQualifiedIsDeletedColumn(),
                operator: '=',
                value: true
            );

            return $builder;
        });
    }
}
