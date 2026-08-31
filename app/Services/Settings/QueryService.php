<?php

namespace App\Services\Settings;

use App\Models\Setting;
use App\Models\User;

class QueryService
{
    /**
     * Get the singleton settings row, creating it with column defaults
     * if it does not yet exist.
     */
    public function current(): Setting
    {
        return Setting::query()->firstOrCreate(['id' => 1]);
    }

    /**
     * Get the settings row along with the acting user's group-level
     * view/edit permissions, ready to pass to the Inertia page.
     *
     * @return array{setting: Setting, permissions: array<string, bool>}
     */
    public function getWithPermissions(User $user): array
    {
        return [
            'setting' => $this->current(),
            'permissions' => [
                'can_view_general' => $user->can('viewGeneral', Setting::class),
                'can_edit_general' => $user->can('updateGeneral', Setting::class),
                'can_view_system' => $user->can('viewSystem', Setting::class),
                'can_edit_system' => $user->can('updateSystem', Setting::class),
                'can_view_security' => $user->can('viewSecurity', Setting::class),
                'can_edit_security' => $user->can('updateSecurity', Setting::class),
            ],
        ];
    }
}
