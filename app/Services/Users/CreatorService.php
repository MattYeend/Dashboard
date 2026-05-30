<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected DataPreparationService $dataPreparation,
        protected LogService $logService
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
            $user = $this->createUser($data);
            $this->logService->logCreation($user, $actor, $createdBy);

            return $user;
        });
    }

    /**
     * Create the user record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createUser(array $data): User
    {
        $userData = $this->dataPreparation->prepareForCreation($data);

        return User::create($userData);
    }
}
