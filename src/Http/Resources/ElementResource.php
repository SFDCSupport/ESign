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
            'eleType' => $this->type,
            'label' => $this->label,
            'on_page' => $this->on_page,
            'left' => $this->left,
            'top' => $this->top,
            'scale_x' => $this->scale_x,
            'scale_y' => $this->scale_y,
            'width' => $this->width,
            'height' => $this->height,
            'position' => $this->position,
        ];
    }
}
