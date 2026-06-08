<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import UserAuditDetails from '@/pages/Users/components/UserAuditDetails.vue';
import UserBasicDetails from '@/pages/Users/components/UserBasicDetails.vue';
import UserRoleDetails from '@/pages/Users/components/UserRoleDetails.vue';
import type { User } from '@/types';

interface Props {
    user: User;
}

const props = defineProps<Props>();

function destroy(): void {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', props.user.id));
    }
}
</script>

<template>
    <AppLayout :title="user.name">
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="items-centre mb-6 flex justify-between">
                    <h1 class="text-grey-900 text-2xl font-semibold">
                        {{ user.name }}
                    </h1>
                    <div class="space-x-2">
                        <a
                            :href="route('users.edit', user.id)"
                            class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium"
                            >Edit</a
                        >
                        <button
                            type="button"
                            class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-red-600"
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
    </AppLayout>
</template>
