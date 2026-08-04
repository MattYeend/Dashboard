<?php

namespace App\Services\Users;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\User;
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
        protected readonly PolicyAuthorisationService $policyAuthorisationService,
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

        $displayRole = $data['role'] ?? 'user';
        $tierRole = User::tierRoleNameFor($displayRole);
        $functionalRoles = $data['roles'] ?? [];

        $this->policyAuthorisationService->authoriseRoleAssignmentOnCreate($actor, $tierRole, $functionalRoles);

        /** @var User $user */
        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor, $tierRole, $functionalRoles): User {
                $userData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newUser = User::create($userData);
                $newUser->plainPassword = $userData['password'];
                $newUser->assignRoles($tierRole, $functionalRoles);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_USER,
                    $actor,
                    $newUser,
                    ['after' => $this->auditLogService->snapshot($newUser)],
                    relatedUser: $newUser,
                );

                return $newUser;
            });
    }
}
