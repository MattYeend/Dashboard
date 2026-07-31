<?php

namespace App\Services\Addresses;

use App\Models\Address;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Stream all matching addresses as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Address::query()->with('addressable');

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($inner) use ($search) {
                $inner->where('address_line_one', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('postcode', 'like', "%{$search}%");
            });
        }

        $columns = [
            'id', 'addressable_type', 'addressable_id', 'address_line_one',
            'address_line_two', 'town', 'city', 'county', 'postcode',
            'country', 'is_primary', 'created_at',
        ];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($addresses) use ($handle, $columns) {
                foreach ($addresses as $address) {
                    fputcsv($handle, $address->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'addresses-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
