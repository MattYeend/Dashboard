<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\Log;
use App\Models\User;

class LogService
{
    /**
     * Log contact creation.
     *
     *
     * @return array<string, mixed>
     */
    public function logCreation(
        Contact $contact,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseContactData($contact) + [
            'created_at' => now(),
            'created_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_CREATE_CONTACT,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a contact show event.
     *
     *
     * @return array<string, mixed>
     */
    public function logShow(
        Contact $contact,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseContactData($contact) + [
            'shown_at' => now(),
            'shown_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_VIEW_CONTACT,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a contact update event.
     *
     *
     * @return array<string, mixed>
     */
    public function logUpdate(
        Contact $contact,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseContactData($contact) + [
            'updated_at' => now(),
            'updated_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_UPDATE_CONTACT,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a contact deletion event.
     *
     *
     * @return array<string, mixed>
     */
    public function logDeletion(
        Contact $contact,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseContactData($contact) + [
            'deleted_at' => now(),
            'deleted_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_DELETE_CONTACT,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log contact force deletion (permanent).
     *
     *
     * @return array<string, mixed>
     */
    public function logForceDeletion(
        Contact $contact,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseContactData($contact) + [
            'force_deleted_at' => now(),
            'force_deleted_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_FORCE_DELETE_CONTACT,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a contact restoration event.
     *
     *
     * @return array<string, mixed>
     */
    public function logRestoration(
        Contact $contact,
        User $actor,
        int $actorId
    ): array {
        $data = $this->baseContactData($contact) + [
            'restored_at' => now(),
            'restored_by' => $actor->name,
        ];

        Log::log(
            Log::ACTION_RESTORE_CONTACT,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a contact import event.
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
            Log::ACTION_IMPORT_CONTACT,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a contact export event.
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
            Log::ACTION_EXPORT_CONTACT,
            $data,
            $actorId,
        );

        return $data;
    }

    /**
     * Log a contact update event performed by a scheduled task (cron).
     *
     *
     * @return array<string, mixed>
     */
    public function logUpdateByCron(Contact $contact): array
    {
        $data = $this->baseContactData($contact) + [
            'updated_at' => now(),
            'updated_by' => 'System (Cron)',
        ];

        Log::log(
            Log::ACTION_CONTACT_UPDATED_BY_CRON,
            $data,
            null,
        );

        return $data;
    }

    /**
     * Get base contact data for logging.
     *
     *
     * @return array<string, mixed>
     */
    protected function baseContactData(Contact $contact): array
    {
        return $this->getContactData($contact);
    }

    /**
     * Get null data for when no contact is available.
     *
     * @return array<string, mixed>
     */
    private function getNullData(): array
    {
        return [
            'id' => null,
            'contactable_type' => null,
            'contactable_id' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
            'city' => null,
            'postal_code' => null,
            'country' => null,
            'meta' => null,
        ];
    }

    /**
     * Get contact data for logging.
     *
     *
     * @return array<string, mixed>
     */
    private function getContactData(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'contactable_type' => $contact->contactable_type,
            'contactable_id' => $contact->contactable_id,
            'phone' => $contact->phone,
            'email' => $contact->email,
            'address' => $contact->address,
            'city' => $contact->city,
            'postal_code' => $contact->postal_code,
            'country' => $contact->country,
            'meta' => $contact->meta,
        ];
    }
}
