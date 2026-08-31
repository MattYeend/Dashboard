<?php

namespace App\Services\Settings;

use App\Http\Requests\Settings\UpdateGeneralSettingRequest;
use App\Http\Requests\Settings\UpdateSecuritySettingRequest;
use App\Http\Requests\Settings\UpdateSystemSettingRequest;
use App\Models\Setting;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly UpdaterService $updater,
    ) {}

    /**
     * Update the general settings group.
     */
    public function updateGeneral(UpdateGeneralSettingRequest $request): Setting
    {
        $data = $this->dataPreparation->prepareGeneral($request->validated());

        return $this->updater->updateGeneral($data, $request->user()->id);
    }

    /**
     * Update the system settings group.
     */
    public function updateSystem(UpdateSystemSettingRequest $request): Setting
    {
        $data = $this->dataPreparation->prepareSystem($request->validated());

        return $this->updater->updateSystem($data, $request->user()->id);
    }

    /**
     * Update the security settings group.
     */
    public function updateSecurity(UpdateSecuritySettingRequest $request): Setting
    {
        $data = $this->dataPreparation->prepareSecurity($request->validated());

        return $this->updater->updateSecurity($data, $request->user()->id);
    }
}
