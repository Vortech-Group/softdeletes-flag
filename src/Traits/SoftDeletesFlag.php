<?php

namespace Vortech\SoftDeletesFlag\Traits;

use Vortech\SoftDeletesFlag\Scopes\SoftDeletesFlagScope;

trait SoftDeletesFlag
{
    protected bool $forceDeleting = false;

    public static function bootSoftDeletesFlag(): void
    {
        static::addGlobalScope(new SoftDeletesFlagScope());
    }

    public function initializeSoftDeletesFlag(): void
    {
        if (! isset($this->casts[$this->getIsDeletedColumn()])) {
            $this->casts[$this->getIsDeletedColumn()] = 'boolean';
        }
    }

    public function forceDelete()
    {
        if ($this->fireModelEvent('forceDeleting') === false) {
            return false;
        }

        $this->forceDeleting = true;

        return tap($this->delete(), function ($deleted) {
            $this->forceDeleting = false;

            if ($deleted) {
                $this->fireModelEvent('forceDeleted', false);
            }
        });
    }

    public function forceDeleteQuietly()
    {
        return static::withoutEvents(fn () => $this->forceDelete());
    }

    protected function performDeleteOnModel()
    {
        if ($this->forceDeleting) {
            return tap($this->setKeysForSaveQuery($this->newModelQuery())->forceDelete(), function () {
                $this->exists = false;
            });
        }

        return $this->runSoftDelete();
    }

    protected function runSoftDelete(): void
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        $columns = [$this->getIsDeletedColumn() => true];

        $this->{$this->getIsDeletedColumn()} = true;

        if ($this->usesTimestamps() && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));

        $this->fireModelEvent('trashed', false);
    }

    public function restore()
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->{$this->getIsDeletedColumn()} = false;

        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    public function restoreQuietly()
    {
        return static::withoutEvents(fn () => $this->restore());
    }

    public function trashed(): bool
    {
        return (bool) $this->{$this->getIsDeletedColumn()};
    }

    public static function softDeleted($callback): void
    {
        static::registerModelEvent('trashed', $callback);
    }


    public static function restoring($callback): void
    {
        static::registerModelEvent('restoring', $callback);
    }

    public static function restored($callback): void
    {
        static::registerModelEvent('restored', $callback);
    }

    public static function forceDeleting($callback): void
    {
        static::registerModelEvent('forceDeleting', $callback);
    }

    public static function forceDeleted($callback): void
    {
        static::registerModelEvent('forceDeleted', $callback);
    }

    public function isForceDeleting(): bool
    {
        return $this->forceDeleting;
    }

    public function getIsDeletedColumn()
    {
        return defined(static::class.'::IS_DELETED') ? static::IS_DELETED : 'is_deleted';
    }

    public function getQualifiedIsDeletedColumn(): string
    {
        return $this->qualifyColumn($this->getIsDeletedColumn());
    }
}
