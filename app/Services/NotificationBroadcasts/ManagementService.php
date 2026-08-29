<?php

namespace App\Services\NotificationBroadcasts;

use App\Actions\SendNotificationBroadcast;
use App\Http\Requests\NotificationBroadcasts\StoreNotificationBroadcastRequest;
use App\Http\Requests\NotificationBroadcasts\UpdateNotificationBroadcastRequest;
use App\Models\NotificationBroadcast;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $deleter,
        protected readonly RestorerService $restorer,
        protected readonly SendNotificationBroadcast $sender,
    ) {}

    /**
     * Create a new notification broadcast.
     */
    public function store(
        StoreNotificationBroadcastRequest $request
    ): NotificationBroadcast {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing notification broadcast.
     */
    public function update(
        UpdateNotificationBroadcastRequest $request,
        NotificationBroadcast $notificationBroadcast
    ): NotificationBroadcast {
        return $this->updater->update(
            $notificationBroadcast,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a notification broadcast.
     */
    public function destroy(
        NotificationBroadcast $notificationBroadcast,
        User $actor
    ): void {
        $this->deleter->delete($notificationBroadcast, $actor->id);
    }

    /**
     * Restore a soft-deleted notification broadcast.
     */
    public function restore(
        int $id,
        User $actor
    ): NotificationBroadcast {
        $notificationBroadcast = NotificationBroadcast::withTrashed()->findOrFail($id);

        return $this->restorer->restore($notificationBroadcast, $actor->id);
    }

    /**
     * Force delete a notification broadcast, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $notificationBroadcast = NotificationBroadcast::withTrashed()->findOrFail($id);
        $this->deleter->forceDelete($notificationBroadcast, $actor->id);
    }

    /**
     * Send a notification broadcast to its configured audience.
     */
    public function send(
        NotificationBroadcast $notificationBroadcast,
        User $actor
    ): NotificationBroadcast {
        return $this->sender->handle($notificationBroadcast, $actor->id);
    }

    /**
     * Bulk restore notification broadcasts.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $notificationBroadcasts = NotificationBroadcast::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            /** @var NotificationBroadcast $notificationBroadcast */
            $authoriseCallback($notificationBroadcast);
            $this->restorer->restore($notificationBroadcast, $actor->id);
            $restored[] = $notificationBroadcast->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($notificationBroadcasts->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete notification broadcasts.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $notificationBroadcasts = NotificationBroadcast::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($notificationBroadcasts as $notificationBroadcast) {
            /** @var NotificationBroadcast $notificationBroadcast */
            $authoriseCallback($notificationBroadcast);
            $this->deleter->delete($notificationBroadcast, $actor->id);
            $deleted[] = $notificationBroadcast->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($notificationBroadcasts->pluck('id'))
                ->values()
                ->all(),
        ];
    }
}
