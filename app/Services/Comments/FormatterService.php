<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\User;

class FormatterService
{
    public function __construct(
        private readonly CommentableTypeRegistryService $registry,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function format(Comment $comment, ?User $viewer = null): array
    {
        return [
            'id' => $comment->id,
            'commentable_type' => $comment->commentable_type,
            'commentable_id' => $comment->commentable_id,
            'commentable_type_key' => $this->registry->keyForModel($comment->commentable_type),
            'commentable_type_label' => $this->registry->labelForModel($comment->commentable_type),
            'commentable_name' => $comment->commentable
                ? ($comment->commentable->name
                    ?? $comment->commentable->title
                    ?? '#'.$comment->commentable->id)
                : null,
            'content' => $comment->content,
            'meta' => $comment->meta,
            'likes_count' => $comment->likes_count ?? 0,
            'liked_by_user' => $comment->relationLoaded('likes')
                ? $comment->likes->isNotEmpty()
                : false,
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
            'can_update' => $viewer ? $viewer->can('update', $comment) : false,
            'can_delete' => $viewer ? $viewer->can('delete', $comment) : false,
        ];
    }
}
