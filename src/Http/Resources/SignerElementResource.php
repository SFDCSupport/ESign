<?php

namespace NIIT\ESign\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignerElementResource extends JsonResource
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
            'page_index' => $this->page_index,
            'page_width' => $this->page_width,
            'page_height' => $this->page_height,
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
