<?php

namespace NIIT\ESign\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use NIIT\ESign\Exceptions\StaleModelLockingException;

trait Versionable
{
    protected $lock = true;

    protected $defaultVersion = 1;

    protected static function lockVersionColumn()
    {
        return 'version';
    }

    public function currentLockVersion()
    {
        return $this->getAttribute(static::lockVersionColumn());
    }

    protected static function defaultLockVersion()
    {
        return 1;
    }

    protected function lockingEnabled()
    {
        return $this->lock ?? true;
    }

    protected function disableLocking()
    {
        $this->lock = false;

        return $this;
    }

    public function enableLocking()
    {
        $this->lock = true;

        return $this;
    }

    //When insert a new record
    protected static function bootOptimisticLocking()
    {
        static::creating(static function (Model $model) {
            if ($model->currentLockVersion() === null) {
                $model->{static::lockVersionColumn()} = static::defaultLockVersion();
            }

            return $model;
        });
    }

    protected function performUpdate(Builder $query)
    {
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $dirty = $this->getDirty();

        if (count($dirty) > 0) {

            $versionColumn = static::lockVersionColumn();

            $this->setKeysForSaveQuery($query);
            if ($this->lockingEnabled()) {
                $query->where($versionColumn, '=', $this->currentLockVersion());
            }

            $beforeUpdateVersion = $this->currentLockVersion();

            $this->setAttribute($versionColumn, $newVersion = $beforeUpdateVersion + 1);
            $dirty[$versionColumn] = $newVersion;

            $affected = $query->update($dirty);
            if ($affected === 0) {
                $this->setAttribute($versionColumn, $beforeUpdateVersion);

                throw new StaleModelLockingException('Model has been changed during update.');
            }

            $this->fireModelEvent('updated', false);

            $this->syncChanges();
        }

        return true;
    }
}
