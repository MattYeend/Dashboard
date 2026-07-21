<script setup lang="ts">
import type { RegistrationInterest } from '@/types';

defineProps<{
    interest: RegistrationInterest;
}>();

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">Audit</h3>
        </div>
        <div class="border-t border-gray-500">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Created</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ formatDate(interest.created_at) }}
                    </dd>
                </div>

                <template v-if="interest.deleted_at">
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-400">
                            Deleted by
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                        >
                            {{ interest.deleter?.name ?? '-' }}
                        </dd>
                    </div>
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-400">
                            Deleted
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                        >
                            {{ formatDate(interest.deleted_at) }}
                        </dd>
                    </div>
                </template>

                <template v-if="interest.restored_at">
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-400">
                            Restored by
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                        >
                            {{ interest.restorer?.name ?? '-' }}
                        </dd>
                    </div>
                    <div
                        class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-400">
                            Restored
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                        >
                            {{ formatDate(interest.restored_at) }}
                        </dd>
                    </div>
                </template>
            </dl>
        </div>
    </div>
</template>
