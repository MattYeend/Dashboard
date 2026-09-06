<?php

namespace App\Services\Comments;

use App\Actions\CreateResource;
use App\Models\Comment;
use App\Models\Log;
use App\Models\User;
use App\Notifications\UserMentionedNotification;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
        protected readonly MentionParserService $mentionParser,
    ) {}

    /**
     * Create a new comment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Comment
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Comment {
                $commentData = $this->dataPreparation->prepareForCreation($data);

                $newComment = Comment::create($commentData);

                $newComment->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_COMMENT,
                    $actor,
                    $newComment,
                    ['after' => $this->auditLogService->snapshot($newComment)],
                );

                $this->notifyMentions($newComment, $commentData['content'] ?? '', $actor);

                return $newComment;
            });
    }

    /**
     * Resolve @mentions in the comment body, persist them against the
     * comment, and notify each mentioned user (excluding the author).
     */
    protected function notifyMentions(Comment $comment, string $body, User $actor): void
    {
        $mentionedUsers = $this->mentionParser->extractMentions($body);

        if ($mentionedUsers->isEmpty()) {
            return;
        }

        $comment->mentions()->sync($mentionedUsers->pluck('id'));

        $mentionedUsers
            ->reject(fn (User $user): bool => $user->id === $actor->id)
            ->each(function (User $user) use ($comment, $actor): void {
                $user->notify(new UserMentionedNotification($comment, $actor));

                $this->auditLogService->record(
                    Log::ACTION_MENTION_NOTIFIED,
                    $actor,
                    $comment,
                    ['after' => ['mentioned_user_id' => $user->id]],
                );
            });
    }
}
