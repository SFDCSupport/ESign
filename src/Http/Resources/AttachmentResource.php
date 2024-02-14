<?php

namespace NIIT\ESign\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model_id' => $this->model_id,
            'model_type' => $this->model_type,
            'type' => $this->type,
            'disk' => $this->disk,
            'bucket' => $this->bucket,
            'file_name' => $this->file_name,
            'url' => $this->url,
            'is_snapshot' => $this->is_snapshot,
            'snapshot_type' => $this->snapshot_type,
        ];
    }
}
