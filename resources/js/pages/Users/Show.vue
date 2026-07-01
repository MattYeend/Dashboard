<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import UserAuditDetails from '@/pages/Users/components/UserAuditDetails.vue';
import UserBasicDetails from '@/pages/Users/components/UserBasicDetails.vue';
import UserRoleDetails from '@/pages/Users/components/UserRoleDetails.vue';
import {
    edit as usersEdit,
    destroy as usersDestroy,
    index as usersIndex,
} from '@/routes/users';
import type { User } from '@/types';

interface Props {
    user: User;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.user?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.user?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(usersDestroy.url(props.user.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ user.name }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="usersIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="usersEdit.url(user.id)"
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
                </div>
            </div>

            <div class="space-y-6">
                <UserBasicDetails :user="user" />
                <UserRoleDetails :user="user" />
                <UserAuditDetails :user="user" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete user"
            description="This user will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
