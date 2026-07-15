<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Passes through the model's own attributes, then layers on the audit
     * and relation data that Companies\QueryService eager-loads.
     */
    public function toArray(Request $request): array
    {
        return array_merge($this->resource->toArray(), [
            'creator' => $this->whenLoaded('creator', fn () => ['name' => $this->creator->name]),
            'updater' => $this->whenLoaded('updater', fn () => ['name' => $this->updater->name]),
            'deleter' => $this->whenLoaded('deleter', fn () => ['name' => $this->deleter->name]),
            'restorer' => $this->whenLoaded('restorer', fn () => ['name' => $this->restorer->name]),
            'industry' => $this->whenLoaded('industry'),
            'accountManager' => $this->whenLoaded('accountManager'),
        ]);
    }
}
