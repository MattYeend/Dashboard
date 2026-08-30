<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PermissionAuditDetails from '@/pages/Permissions/components/PermissionAuditDetails.vue';
import PermissionBasicDetails from '@/pages/Permissions/components/PermissionBasicDetails.vue';
import PermissionRoleDetails from '@/pages/Permissions/components/PermissionRoleDetails.vue';
import {
    edit as permissionsEdit,
    destroy as permissionsDestroy,
    restore as permissionsRestore,
    forceDelete as permissionsForceDelete,
    index as permissionsIndex,
} from '@/routes/permissions';
import type { Permission, PermissionsMeta } from '@/types';

interface Props {
    permission: Permission;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

const restoreDialogOpen = ref(false);
const restoreProcessing = ref(false);

const forceDeleteDialogOpen = ref(false);
const forceDeleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.permission?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.permission?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(permissionsDestroy.url(props.permission.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

function requestRestore(): void {
    if (!props.permission?.id) {
        return;
    }

    restoreDialogOpen.value = true;
}

function restore(): void {
    if (!props.permission?.id) {
        return;
    }

    restoreProcessing.value = true;

    router.post(
        permissionsRestore.url({ id: props.permission.id }),
        {},
        {
            onFinish: () => {
                restoreProcessing.value = false;
                restoreDialogOpen.value = false;
            },
        },
    );
}

function requestForceDelete(): void {
    if (!props.permission?.id) {
        return;
    }

    forceDeleteDialogOpen.value = true;
}

function forceDelete(): void {
    if (!props.permission?.id) {
        return;
    }

    forceDeleteProcessing.value = true;

    router.delete(permissionsForceDelete.url({ id: props.permission.id }), {
        onFinish: () => {
            forceDeleteProcessing.value = false;
            forceDeleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ permission.name }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="permissionsIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>

                    <template v-if="!permission.deleted_at">
                        <Link
                            :href="permissionsEdit.url(permission.id)"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                        >
                            Edit
                        </Link>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                            @click="requestDestroy"
                        >
                            Delete
                        </button>
                    </template>
                    <template v-else>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                            @click="requestRestore"
                        >
                            Restore
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                            @click="requestForceDelete"
                        >
                            Delete permanently
                        </button>
                    </template>
                </div>
            </div>

            <div class="space-y-6">
                <PermissionBasicDetails :permission="permission" />
                <PermissionRoleDetails :permission="permission" />
                <PermissionAuditDetails :permission="permission" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete permission"
            description="This permission will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />

        <ConfirmDialog
            v-model:open="restoreDialogOpen"
            title="Restore permission"
            description="This permission will be restored from trash."
            confirm-label="Restore"
            :processing="restoreProcessing"
            @confirm="restore"
        />

        <ConfirmDialog
            v-model:open="forceDeleteDialogOpen"
            title="Permanently delete permission"
            description="This cannot be undone. The permission will be permanently removed."
            confirm-label="Delete permanently"
            :processing="forceDeleteProcessing"
            @confirm="forceDelete"
        />
    </div>
</template>
