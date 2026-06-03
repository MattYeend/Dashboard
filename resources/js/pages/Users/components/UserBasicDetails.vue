<script setup lang="ts">
import type { User } from '@/types';

interface Props {
    user: User;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-grey-900">Basic Details</h3>
        </div>
        <div class="border-t border-grey-200">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Name</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.name }}</dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Email Address</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">{{ user.email }}</dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-grey-500">Email Verified</dt>
                    <dd class="mt-1 text-sm text-grey-900 sm:col-span-2 sm:mt-0">
                        {{ user.email_verified_at ? user.email_verified_at : 'Not verified' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>