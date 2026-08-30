<script setup lang="ts">
import type { RoleOption } from '@/types';

interface MatrixPermission {
    id: number;
    name: string;
    role_ids: number[];
}

interface Props {
    permissions: MatrixPermission[];
    roles: RoleOption[];
}

defineProps<Props>();

const assignments = defineModel<Record<number, number[]>>('assignments', {
    required: true,
});

function isChecked(permissionId: number, roleId: number): boolean {
    return assignments.value[permissionId]?.includes(roleId) ?? false;
}

function toggle(permissionId: number, roleId: number, checked: boolean) {
    const current = assignments.value[permissionId] ?? [];
    assignments.value = {
        ...assignments.value,
        [permissionId]: checked
            ? [...current, roleId]
            : current.filter((id) => id !== roleId),
    };
}
</script>

<template>
    <table class="w-full border-collapse text-sm">
        <thead>
            <tr>
                <th class="border-b border-gray-500 p-2 text-left">
                    Permission
                </th>
                <th
                    v-for="role in roles"
                    :key="role.id"
                    class="border-b border-gray-500 p-2 text-center"
                >
                    {{ role.name }}
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="permission in permissions" :key="permission.id">
                <td class="border-b border-gray-500 p-2">
                    {{ permission.name }}
                </td>
                <td
                    v-for="role in roles"
                    :key="role.id"
                    class="border-b border-gray-500 p-2 text-center"
                >
                    <input
                        type="checkbox"
                        :checked="isChecked(permission.id, role.id)"
                        @change="
                            (e) =>
                                toggle(
                                    permission.id,
                                    role.id,
                                    (e.target as HTMLInputElement).checked,
                                )
                        "
                    />
                </td>
            </tr>
        </tbody>
    </table>
</template>
