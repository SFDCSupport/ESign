<?php

namespace NIIT\ESign\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'signer_id' => $this->signer_id,
            'type' => $this->type,
            'label' => $this->label,
            'on_page' => $this->on_page,
            'offset_x' => $this->offset_x,
            'offset_y' => $this->offset_y,
            'width' => $this->width,
            'height' => $this->height,
            'position' => $this->position,
        ];
    }
}
