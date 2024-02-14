<?php

namespace NIIT\ESign\Models;

use App\Actions\FilepondAction;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
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
        'version',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'version' => 'integer',
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
            get: fn (?string $value, array $attributes) => FilepondAction::loadFile($attributes['path'], 'view'),
        );
    }

    public function createSnapshotFor(Signer $signer, SnapshotType $type)
    {
        $newFileName = $type->value.'-'.$this->file_name;

        Storage::disk($this->disk)->copy(
            $this->path,
            ($path = "esign/{$signer->document_id}/snapshots/{$signer->id}/{$newFileName}")
        );

        $newModel = $this->replicate([
            'model_id',
            'model_type',
            'type',
            'is_snapshot',
            'snapshot_type',
        ]);

        $newModel->model_id = $signer->id;
        $newModel->model_type = Signer::class;
        $newModel->is_snapshot = true;
        $newModel->snapshot_type = $type;
        $newModel->path = $path;
        $newModel->file_name = $newFileName;

        $newModel->push();

    }
}
