<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     *
     * @param  DataPreparationService $dataPreparation
     * @param  LogService $logService
     */
    public function __construct(
        protected DataPreparationService $dataPreparation,
        protected LogService $logService
    ) {
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed> $data
     * @param  int $createdBy
     *
     * @return User
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): User
    {
        $actor = User::findOrFail($createdBy);

        return DB::transaction(function () use ($data, $createdBy, $actor) {
            $user = $this->createContact($data);
            $this->logService->logCreation($user, $actor, $createdBy);

            return $user;
        });
    }

    /**
     * Create the user record.
     *
     * @param  array<string, mixed> $data
     *
     * @return User
     */
    protected function createContact(array $data): User
    {
        $contactData = $this->dataPreparation->prepareForCreation(
            $data,
            $data['contactable_type'],
            $data['contactable_id']
        );

        return User::create($contactData);
    }
}
