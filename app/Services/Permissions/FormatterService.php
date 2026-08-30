<?php

namespace App\Services\Permissions;

use App\Models\Permission;

class FormatterService
{
    /**
     * Format a single permission with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'meta' => $permission->meta,
            'created_at' => $permission->created_at,
            'updated_at' => $permission->updated_at,
            'deleted_at' => $permission->deleted_at,
            'restored_at' => $permission->restored_at,
            'roles' => $permission->relationLoaded('roles')
                ? $permission->roles->map(fn ($role) => ['id' => $role->id, 'name' => $role->name])->all()
                : [],
            'creator' => $permission->creator ? ['id' => $permission->creator->id, 'name' => $permission->creator->name] : null,
            'updater' => $permission->updater ? ['id' => $permission->updater->id, 'name' => $permission->updater->name] : null,
            'deleter' => $permission->deleter ? ['id' => $permission->deleter->id, 'name' => $permission->deleter->name] : null,
            'restorer' => $permission->restorer ? ['id' => $permission->restorer->id, 'name' => $permission->restorer->name] : null,
        ];
    }
}
