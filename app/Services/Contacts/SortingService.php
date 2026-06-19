<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<Contact>  $query
     * @return Builder<Contact>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower(
            $sortDirection ?? 'asc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'phone' => $query->orderBy('phone', $sortDirection),
            'email' => $query->orderBy('email', $sortDirection),
            'address' => $query->orderBy('address', $sortDirection),
            'city' => $query->orderBy('city', $sortDirection),
            'postal_code' => $query->orderBy('postal_code', $sortDirection),
            'country' => $query->orderBy('country', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            default => $query->orderBy('created_at', $sortDirection),
        };
    }

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'phone' => 'Phone',
            'email' => 'Email',
            'address' => 'Address',
            'city' => 'City',
            'postal_code' => 'Postal Code',
            'country' => 'Country',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
        ];
    }
}
