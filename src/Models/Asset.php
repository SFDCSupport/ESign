<?php

namespace NIIT\ESign\Models;

use App\Actions\FilepondAction;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use NIIT\ESign\Enum\AssetType;
use NIIT\ESign\Enum\SnapshotType;

class Asset extends Model
{
    /** @var string */
    protected $table = 'e_assets';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id',
        'model_type',
        'model_id',
        'type',
        'disk',
        'bucket',
        'file_name',
        'extension',
        'path',
        'is_snapshot',
        'snapshot_type',
        'updated_at',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'is_snapshot' => 'boolean',
        'type' => AssetType::class,
        'snapshot_type' => SnapshotType::class,
    ];

    protected $appends = [
        'url',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo(
            type: 'model_type',
            id: 'model_id',
        );
    }

    public function url(): Attribute
    {
        return new Attribute(
            get: fn (?string $value, array $attributes) => FilepondAction::loadFile($attributes['path'].'/'.$attributes['file_name'], 'view'),
        );
    }
}
