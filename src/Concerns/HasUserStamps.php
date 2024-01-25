<?php

namespace NIIT\ESign\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUserStamps
{
    protected static $hasUserStamps = true;

    public static function bootHasUserStamps(): void
    {
        if (! static::$hasUserStamps) {
            return;
        }

        static::creating(function ($model) {
            $model->created_by = optional(getOriginalUser())->id;
        });

        static::updating(function ($model) {
            $model->updated_by = optional(getOriginalUser())->id;
        });

        if (static::usingSoftDeletes()) {
            static::deleting(function ($model) {
                $model->restored_at = null;
                $model->deleted_by = optional(getOriginalUser())->id;
            });

            static::restoring(function ($model) {
                $model->restored_at = now();
                $model->restored_by = optional(getOriginalUser())->id;
            });
        }
    }

    public function initializeHasUserStamps(): void
    {
        $this->fillable[] = 'restored_at';
        $this->fillable[] = 'created_by';
        $this->fillable[] = 'updated_by';
        $this->fillable[] = 'deleted_by';
        $this->fillable[] = 'restored_by';

        if (! isset($this->casts['restored_at'])) {
            $this->casts['restored_at'] = 'datetime';
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo($this->getUserClass(), 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo($this->getUserClass(), 'updated_by');
    }

    public function destroyer(): BelongsTo
    {
        return $this->belongsTo($this->getUserClass(), 'deleted_by');
    }

    public function restorer(): BelongsTo
    {
        return $this->belongsTo($this->getUserClass(), 'restored_by');
    }

    public static function usingSoftDeletes(): bool
    {
        static $usingSoftDeletes;

        if (is_null($usingSoftDeletes)) {
            return $usingSoftDeletes = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class), true);
        }

        return $usingSoftDeletes;
    }

    protected function getUserClass(): string
    {
        return config('auth.providers.users.model');
    }
}
