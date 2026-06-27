<?php

namespace App\Services\Users;

use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService
    ) {}

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): User
    {
        $actor = User::findOrFail($createdBy);

        return DB::transaction(function () use ($data, $createdBy, $actor) {
            $user = $this->createUser($data, $createdBy);
            $this->auditLogService->record(
                Log::ACTION_CREATE_USER,
                $actor,
                $user,
                ['before' => $user->toArray()],
                relatedUser: $user,
            );

            return $user;
        });
    }

    /**
     * Create the user record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createUser(array $data, int $createdBy): User
    {
        $userData = $this->dataPreparation->prepareForCreation($data, $createdBy);
        $displayRole = $userData['role'] ?? 'user';

        $user = User::create($userData);
        $user->assignApplicationRole($displayRole);

        return $user->fresh();
    }
}
