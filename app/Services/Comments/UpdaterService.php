<?php

namespace App\Services\Comments;

use App\Actions\UpdateResource;
use App\Models\Comment;
use App\Models\Log;
use App\Models\User;
use App\Notifications\UserMentionedNotification;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
        protected readonly MentionParserService $mentionParser,
    ) {}

    /**
     * Update an existing comment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Comment $comment,
        array $data,
        int $updatedBy
    ): Comment {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($comment);
        $previouslyMentionedIds = $comment->mentions()->pluck('users.id');

        $commentData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $comment,
            $commentData,
            function (Comment $comment) use ($actor, $before, $updatedBy, $previouslyMentionedIds, $commentData): void {
                $comment->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $comment->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_COMMENT,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );

                $this->notifyNewMentions($fresh, $commentData['content'] ?? '', $actor, $previouslyMentionedIds);
            });
    }

    /**
     * Resolve @mentions in the updated body, persist them against the
     * comment, and notify only newly-added mentions (excluding the author),
     * so editing a comment does not re-notify users mentioned previously.
     *
     * @param  Collection<int, int>  $previouslyMentionedIds
     */
    protected function notifyNewMentions(
        Comment $comment,
        string $body,
        User $actor,
        Collection $previouslyMentionedIds
    ): void {
        $mentionedUsers = $this->mentionParser->extractMentions($body);

        $comment->mentions()->sync($mentionedUsers->pluck('id'));

        $mentionedUsers
            ->reject(fn (User $user): bool => $user->id === $actor->id
                || $previouslyMentionedIds->contains($user->id))
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
