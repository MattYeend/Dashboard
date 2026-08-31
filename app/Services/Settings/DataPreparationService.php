<?php

namespace App\Services\Settings;

class DataPreparationService
{
    /**
     * Restrict the payload to the general settings group's allowed fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareGeneral(array $data): array
    {
        return $this->only($data, [
            'site_name',
            'support_email',
            'timezone',
            'date_format',
        ]);
    }

    /**
     * Restrict the payload to the system settings group's allowed fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareSystem(array $data): array
    {
        return $this->only($data, [
            'maintenance_mode',
            'allow_registrations',
            'default_pagination',
            'default_locale',
        ]);
    }

    /**
     * Restrict the payload to the security settings group's allowed fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareSecurity(array $data): array
    {
        return $this->only($data, [
            'two_factor_required',
            'session_timeout_minutes',
            'max_login_attempts',
            'password_expiry_days',
        ]);
    }

    /**
     * Filter an array down to a fixed allow-list of keys.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $allowed
     * @return array<string, mixed>
     */
    private function only(array $data, array $allowed): array
    {
        return array_intersect_key($data, array_flip($allowed));
    }
}
