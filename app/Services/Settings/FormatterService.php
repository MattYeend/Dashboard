<?php

namespace App\Services\Settings;

use App\Models\Setting;

class FormatterService
{
    /**
     * Format the settings row for use as an Inertia prop.
     *
     * @return array<string, mixed>
     */
    public function format(Setting $setting): array
    {
        return [
            'id' => $setting->id,
            'site_name' => $setting->site_name,
            'support_email' => $setting->support_email,
            'timezone' => $setting->timezone,
            'date_format' => $setting->date_format,
            'maintenance_mode' => $setting->maintenance_mode,
            'allow_registrations' => $setting->allow_registrations,
            'default_pagination' => $setting->default_pagination,
            'default_locale' => $setting->default_locale,
            'two_factor_required' => $setting->two_factor_required,
            'session_timeout_minutes' => $setting->session_timeout_minutes,
            'max_login_attempts' => $setting->max_login_attempts,
            'password_expiry_days' => $setting->password_expiry_days,
            'updater' => $setting->updater ? ['id' => $setting->updater->id, 'name' => $setting->updater->name] : null,
            'updated_at' => $setting->updated_at,
        ];
    }
}
