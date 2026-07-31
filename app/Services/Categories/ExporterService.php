<?php

namespace App\Services\Categories;

use App\Models\Category;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Stream all matching categories as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Category::query();

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where('name', 'like', "%{$search}%");
        }

        $columns = ['id', 'name', 'slug', 'description', 'parent_id', 'created_at'];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($categories) use ($handle, $columns) {
                foreach ($categories as $category) {
                    fputcsv($handle, $category->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'categories-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
