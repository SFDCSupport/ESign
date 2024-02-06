<?php

namespace NIIT\ESign\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'text' => $this->text,
            'signing_status' => $this->signing_status,
            'read_status' => $this->read_status,
            'send_status' => $this->send_status,
            'position' => $this->position,
            'is_next_receiver' => $this->is_next_receiver,
            'elements' => SignerElementResource::collection(
                $this->whenLoaded('elements'),
            ),
        ];
    }
}
