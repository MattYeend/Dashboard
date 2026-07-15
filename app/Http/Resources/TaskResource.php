<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date,
            'assigned_date' => $this->assigned_date,
            'assigned_to' => $this->assigned_to,
            'status_id' => $this->status_id,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'restored_at' => $this->restored_at,
            'assignee' => $this->whenLoaded('assignee', fn () => ['id' => $this->assignee->id, 'name' => $this->assignee->name]),
            'status' => $this->whenLoaded('status'),
            'creator' => $this->whenLoaded('creator', fn () => ['name' => $this->creator->name]),
            'updater' => $this->whenLoaded('updater', fn () => ['name' => $this->updater->name]),
            'deleter' => $this->whenLoaded('deleter', fn () => ['name' => $this->deleter->name]),
            'restorer' => $this->whenLoaded('restorer', fn () => ['name' => $this->restorer->name]),
        ];
    }
}
