<script setup lang="ts">
import type { User } from '@/types';

interface Props {
    user: User;
}

defineProps<Props>();

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
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
            <h3 class="text-gray-300 text-lg leading-6 font-medium">
                Basic Details
            </h3>
        </div>
        <div class="border-gray-500 border-t">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-gray-400 text-sm font-medium">Name</dt>
                    <dd
                        class="text-gray-300 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ user.name }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-gray-400 text-sm font-medium">
                        Email Address
                    </dt>
                    <dd
                        class="text-gray-300 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{ user.email }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-gray-400 text-sm font-medium">
                        Email Verified
                    </dt>
                    <dd
                        class="text-gray-300 mt-1 text-sm sm:col-span-2 sm:mt-0"
                    >
                        {{
                            user.email_verified_at
                                ? formatDate(user.email_verified_at)
                                : 'Not verified'
                        }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
