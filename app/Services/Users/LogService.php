<?php

namespace App\Services\Users;

use App\Models\Log;
use App\Models\User;

class LogService
{
    /**
     * Log user creation.
     *
     * @return array<string, mixed>
     */
    public function logCreation(
        User $user,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseUserData($user) + [
            'created_at' => now(),
            'created_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_CREATE_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user show event.
     *
     * @return array<string, mixed>
     */
    public function logShow(
        User $user,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseUserData($user) + [
            'shown_at' => now(),
            'shown_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_VIEW_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user update event.
     *
     * @return array<string, mixed>
     */
    public function logUpdate(
        User $user,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseUserData($user) + [
            'updated_at' => now(),
            'updated_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_UPDATE_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user deletion event.
     *
     * @return array<string, mixed>
     */
    public function logDeletion(
        User $user,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseUserData($user) + [
            'deleted_at' => now(),
            'deleted_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_DELETE_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log user force deletion (permanent).
     *
     * @return array<string, mixed>
     */
    public function logForceDeletion(
        User $user,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseUserData($user) + [
            'force_deleted_at' => now(),
            'force_deleted_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_FORCE_DELETE_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user restoration event.
     *
     * @return array<string, mixed>
     */
    public function logRestoration(
        User $user,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseUserData($user) + [
            'restored_at' => now(),
            'restored_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_RESTORE_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user import event.
     *
     * @param  array<int, mixed>  $importData
     * @return array<string, mixed>
     */
    public function logImport(
        array $importData,
        User $actor,
        int $actorId
    ): array {
        $data = [
            'imported_at' => now(),
            'imported_by' => $actor->name,
            'imported_count' => count($importData),
            'imported_data_sample' => array_slice($importData, 0, 5),
        ];

        Log::log(
            Log::ACTION_IMPORT_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user export event.
     *
     * @param  array<int, mixed>  $exportData
     * @return array<string, mixed>
     */
    public function logExport(
        array $exportData,
        User $actor,
        int $actorId
    ): array {
        $data = [
            'exported_at' => now(),
            'exported_by' => $actor->name,
            'exported_count' => count($exportData),
            'exported_data_sample' => array_slice($exportData, 0, 5),
        ];

        Log::log(
            Log::ACTION_EXPORT_USER,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a user update event performed by a scheduled task (cron).
     *
     * @return array<string, mixed>
     */
    public function logUpdateByCron(User $user): array
    {
        $data = $this->baseUserData($user) + [
            'updated_at' => now(),
            'updated_by' => 'System (Cron)',
        ];

        Log::log(
            Log::ACTION_USER_UPDATED_BY_CRON,
            $data,
            null,
        );

        return $data;
    }

    /**
     * Get base user data for logging.
     *
     * @return array<string, mixed>
     */
    protected function baseUserData(User $user): array
    {
        return $this->getUserData($user);
    }

    /**
     * Get null data for when no user is available.
     *
     * @return array<string, mixed>
     */
    private function getNullData(): array
    {
        return [
            'id' => null,
            'name' => null,
            'email' => null,
            'email_verified_at' => null,
            'role' => null,
            'meta' => null,
        ];
    }

    /**
     * Get user data for logging.
     *
     * @return array<string, mixed>
     */
    private function getUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'role' => $user->role,
            'meta' => $user->meta,
        ];
    }
}
