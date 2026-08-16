<?php

namespace App\Services\Posts;

use App\Models\Log;
use App\Models\Post;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Stream all matching posts as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Post::query()->with(['categories', 'tags']);

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $this->auditLogService->record(
            Log::ACTION_EXPORT_POST,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'id',
                'title',
                'description',
                'image',
                'categories',
                'tags',
                'created_by',
                'created_at',
            ]);

            $query->orderBy('id')->chunk(500, function ($posts) use ($handle) {
                foreach ($posts as $post) {
                    fputcsv($handle, [
                        $post->id,
                        $post->title,
                        $post->description,
                        $post->image,
                        $post->categories->pluck('name')->implode(', '),
                        $post->tags->pluck('name')->implode(', '),
                        $post->created_at,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'posts-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
