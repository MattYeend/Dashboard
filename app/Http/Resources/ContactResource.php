<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contactable_type' => $this->contactable_type,
            'contactable_id' => $this->contactable_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'restored_at' => $this->restored_at,
            'contactable' => $this->whenLoaded('contactable'),
            'creator' => $this->whenLoaded('creator', fn () => ['name' => $this->creator->name]),
            'updater' => $this->whenLoaded('updater', fn () => ['name' => $this->updater->name]),
            'deleter' => $this->whenLoaded('deleter', fn () => ['name' => $this->deleter->name]),
            'restorer' => $this->whenLoaded('restorer', fn () => ['name' => $this->restorer->name]),
        ];
    }
}
