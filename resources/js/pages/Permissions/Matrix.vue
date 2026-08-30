<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import PermissionRoleMatrix from '@/pages/Permissions/components/PermissionRoleMatrix.vue';
import permissionsMatrix from '@/routes/permissions/matrix';
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

const props = defineProps<Props>();

const assignments = ref<Record<number, number[]>>(
    Object.fromEntries(
        props.permissions.map((permission) => [
            permission.id,
            permission.role_ids,
        ]),
    ),
);

const saving = ref(false);

function save(): void {
    saving.value = true;

    router.patch(
        permissionsMatrix.update.url(),
        {
            assignments: Object.entries(assignments.value).map(
                ([permissionId, roleIds]) => ({
                    permission_id: Number(permissionId),
                    role_ids: roleIds,
                }),
            ),
        },
        {
            preserveScroll: true,
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-lg font-semibold">
                Permission and role matrix
            </h1>
            <PermissionRoleMatrix
                v-model:assignments="assignments"
                :permissions="props.permissions"
                :roles="props.roles"
            />
            <Button class="mt-6" @click="save" :disabled="saving">
                Save assignments
            </Button>
        </div>
    </div>
</template>
