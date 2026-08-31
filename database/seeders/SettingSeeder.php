<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::query()->firstOrCreate(['id' => 1], [
            'site_name' => 'Dashboard',
            'support_email' => 'mail@mattyeend.co.uk',
            'timezone' => 'Europe/London',
            'date_format' => 'd/m/Y',
            'maintenance_mode' => false,
            'allow_registrations' => true,
            'default_pagination' => 15,
            'default_locale' => 'en_GB',
            'two_factor_required' => false,
            'session_timeout_minutes' => 120,
            'max_login_attempts' => 5,
            'password_expiry_days' => null,
        ]);
    }
}
