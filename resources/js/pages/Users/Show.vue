<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
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

function destroy(): void {
    if (!props.user?.id) {
        return;
    }

    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(usersDestroy.url(props.user.id));
    }
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="items-center mb-6 flex justify-between">
                <h1 class="text-gray-300 text-2xl font-semibold">
                    {{ user.name }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="usersIndex.url()"
                        class="items-center inline-flex rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="usersEdit.url(user.id)"
                        class="items-center inline-flex rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        type="button"
                        class="items-center inline-flex rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="destroy"
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
    </div>
</template>
