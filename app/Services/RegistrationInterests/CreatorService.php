<?php

namespace App\Services\RegistrationInterests;

use App\Actions\CreateResource;
use App\Actions\RegistrationInterests\NotifyAdminOfNewRegistrationInterest;
use App\Models\Log;
use App\Models\RegistrationInterest;

class CreatorService
{
    public function __construct(
        protected CreateResource $createResource,
        protected NotifyAdminOfNewRegistrationInterest $notifyAdmin,
    ) {}

    /**
     * Create a new registration interest and notify admins.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): RegistrationInterest
    {
        $interest = $this->createResource->handle($data, function (array $data): RegistrationInterest {
            $interest = RegistrationInterest::create($data);

            Log::log(Log::ACTION_CREATE_REGISTRATION_INTEREST, $interest->auditSnapshot());

            return $interest;
        });

        $this->notifyAdmin->execute($interest);

        return $interest;
    }
}
