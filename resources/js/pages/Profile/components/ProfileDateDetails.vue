<script setup lang="ts">
import type { User } from '@/types';

defineProps<{
    user: User;
}>();

function formatDate(value: string | null): string {
    if (!value) {
        return 'Not available';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}
</script>

<template>
    <section class="rounded-lg border border-gray-500 p-4">
        <h2 class="mb-3 text-sm font-medium text-gray-300">Dates</h2>
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Member since</dt>
                <dd class="text-sm">{{ formatDate(user.created_at) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Last updated</dt>
                <dd class="text-sm">{{ formatDate(user.updated_at) }}</dd>
            </div>
        </dl>
    </section>
</template>