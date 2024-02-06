<?php

namespace NIIT\ESign\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUserStamps
{
    protected static bool $hasUserStamps = true;

    /**
     * @var array<int,string>
     */
    protected static array $stampingColumns = [
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'restored_at',
    ];

    public static function bootHasUserStamps(): void
    {
        if (! static::$hasUserStamps) {
            return;
        }

        if (static::hasStampingCol('created_by')) {
            static::creating(static function ($model) {
                $model->created_by = optional(getOriginalUser())->id;
            });
        }

        if (static::hasStampingCol('updated_by')) {
            static::updating(static function ($model) {
                $model->updated_by = optional(getOriginalUser())->id;
            });
        }

        if (static::usingSoftDeletes()) {
            static::deleting(static function ($model) {
                if (static::hasStampingCol('restored_at')) {
                    $model->restored_at = null;
                }

                if (static::hasStampingCol('deleted_by')) {
                    $model->deleted_by = optional(getOriginalUser())->id;
                }
            });

            static::restoring(static function ($model) {
                if (static::hasStampingCol('restored_at')) {
                    $model->restored_at = now();
                }
                if (static::hasStampingCol('restored_by')) {
                    $model->restored_by = optional(getOriginalUser())->id;
                }
            });
        }
    }

    public function disableStamping(): self
    {
        static::$hasUserStamps = false;

        return $this;
    }

    public function enableStamping(): self
    {
        static::$hasUserStamps = true;

        return $this;
    }

    public function initializeHasUserStamps(): void
    {
        $this->fillable = array_merge($this->fillable, static::$stampingColumns);

        if (
            ! isset($this->casts['restored_at']) &&
            static::hasStampingCol('restored_at')
        ) {
            $this->fillable[] = 'restored_at';
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

    protected static function hasStampingCol(array|string $col): bool
    {
        return is_array($col)
            ? ! empty(array_diff($col, static::$stampingColumns))
            : in_array($col, static::$stampingColumns, true);
    }
}
