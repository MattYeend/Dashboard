<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\User;

class FormatterService
{
    /**
     * Format a single comment with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Comment $comment, ?User $viewer = null): array
    {
        return [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'content' => $comment->content,
            'meta' => $comment->meta,
            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
            'deleted_at' => $comment->deleted_at,
            'restored_at' => $comment->restored_at,
            'created_by' => $comment->created_by,
            'updated_by' => $comment->updated_by,
            'deleted_by' => $comment->deleted_by,
            'restored_by' => $comment->restored_by,
            'creator' => $comment->creator ? ['id' => $comment->creator->id, 'name' => $comment->creator->name] : null,
            'updater' => $comment->updater ? ['id' => $comment->updater->id, 'name' => $comment->updater->name] : null,
            'deleter' => $comment->deleter ? ['id' => $comment->deleter->id, 'name' => $comment->deleter->name] : null,
            'restorer' => $comment->restorer ? ['id' => $comment->restorer->id, 'name' => $comment->restorer->name] : null,
            'can_delete' => $viewer ? $viewer->can('delete', $comment) : false,
        ];
    }
}
