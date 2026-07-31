<?php

namespace App\Services\Comments;

use App\Models\Post;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Stream comments belonging to the given post as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(Post $post, array $filters): StreamedResponse
    {
        $query = $post->comments();

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $query->where('content', 'like', '%'.$filters['search'].'%');
        }

        $columns = ['id', 'post_id', 'content', 'created_by', 'created_at'];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($comments) use ($handle, $columns) {
                foreach ($comments as $comment) {
                    fputcsv($handle, $comment->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            "post-{$post->id}-comments-".now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
