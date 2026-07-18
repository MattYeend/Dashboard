<?php

namespace App\Services\Posts;

use App\Models\Post;

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
            'image' => $post->image,
            'meta' => $post->meta,
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
}