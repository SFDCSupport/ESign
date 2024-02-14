<?php

namespace NIIT\ESign\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'document' => new AssetResource($this->whenLoaded('document')),
            'signers' => SignerResource::collection(
                $this->whenLoaded('signers'),
            ),
            'status' => $this->status,
            'notification_sequence' => $this->notification_sequence,
        ];
    }
}
