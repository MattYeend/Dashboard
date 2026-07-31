<?php

namespace App\Services\Companies;

use App\Models\Company;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Stream all matching companies as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Company::query()->with('industry');

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $columns = [
            'id', 'name', 'slug', 'email', 'phone', 'website',
            'registration_number', 'vat_number', 'description',
            'industry_id', 'account_manager_id', 'employee_count',
            'founded_year', 'created_at',
        ];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($companies) use ($handle, $columns) {
                foreach ($companies as $company) {
                    fputcsv($handle, $company->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'companies-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
