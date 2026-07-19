<?php

namespace App\Services\Posts;

use App\Models\Post;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class FormatterService
{
    /**
     * Format a single post with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image' => $this->formatImageUrl($post->image),
            'meta' => $post->meta,
            'categories' => $post->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])->all(),
            'likes_count' => $post->likes_count ?? 0,
            'liked_by_user' => $post->relationLoaded('likes')
                ? $post->likes->isNotEmpty()
                : false,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'deleted_at' => $post->deleted_at,
            'restored_at' => $post->restored_at,
            'created_by' => $post->created_by,
            'updated_by' => $post->updated_by,
            'deleted_by' => $post->deleted_by,
            'restored_by' => $post->restored_by,
            'creator' => $post->creator ? ['id' => $post->creator->id, 'name' => $post->creator->name] : null,
            'updater' => $post->updater ? ['id' => $post->updater->id, 'name' => $post->updater->name] : null,
            'deleter' => $post->deleter ? ['id' => $post->deleter->id, 'name' => $post->deleter->name] : null,
            'restorer' => $post->restorer ? ['id' => $post->restorer->id, 'name' => $post->restorer->name] : null,
        ];
    }

    /**
     * Resolve the public URL for a stored image path.
     */
    private function formatImageUrl(?string $path): ?string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $path ? $disk->url($path) : null;
    }
}
