<?php

namespace App\Services\RegistrationInterests;

use App\Models\RegistrationInterest;
use Illuminate\Database\Eloquent\Collection;

class FormatterService
{
    /**
     * Format a single registration interest for the frontend.
     *
     * @return array<string, mixed>
     */
    public function format(RegistrationInterest $interest): array
    {
        return [
            'id' => $interest->id,
            'name' => $interest->name,
            'email' => $interest->email,
            'phone' => $interest->phone,
            'company' => $interest->company,
            'message' => $interest->message,
            'created_at' => $interest->created_at,
            'deleted_at' => $interest->deleted_at,
        ];
    }

    /**
     * Format a collection of registration interests for the frontend.
     *
     * @param  Collection<int, RegistrationInterest>  $interests
     * @return array<int, array<string, mixed>>
     */
    public function formatMany(Collection $interests): array
    {
        return $interests->map(fn (RegistrationInterest $interest) => $this->format($interest))->all();
    }
}
