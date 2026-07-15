<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'orderable_type' => $this->orderable_type,
            'orderable_id' => $this->orderable_id,
            'order_number' => $this->order_number,
            'title' => $this->title,
            'description' => $this->description,
            'notes' => $this->notes,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'ordered_at' => $this->ordered_at,
            'due_at' => $this->due_at,
            'completed_at' => $this->completed_at,
            'status_id' => $this->status_id,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'restored_at' => $this->restored_at,
            'orderable' => $this->whenLoaded('orderable'),
            'status' => $this->whenLoaded('status'),
            'creator' => $this->whenLoaded('creator', fn () => ['name' => $this->creator->name]),
            'updater' => $this->whenLoaded('updater', fn () => ['name' => $this->updater->name]),
            'deleter' => $this->whenLoaded('deleter', fn () => ['name' => $this->deleter->name]),
            'restorer' => $this->whenLoaded('restorer', fn () => ['name' => $this->restorer->name]),
        ];
    }
}
