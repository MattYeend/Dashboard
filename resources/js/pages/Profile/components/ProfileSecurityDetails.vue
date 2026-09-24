<script setup lang="ts">
import type { User } from '@/types';

defineProps<{
    user: User & { two_factor_enabled: boolean };
}>();
</script>

<template>
    <section class="rounded-lg border border-gray-500 p-4">
        <h2 class="mb-3 text-sm font-medium text-gray-300">Security</h2>
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Email verified</dt>
                <dd class="text-sm">{{ user.email_verified_at ? 'Yes' : 'No' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Two-factor authentication</dt>
                <dd class="text-sm">{{ user.two_factor_enabled ? 'Enabled' : 'Not enabled' }}</dd>
            </div>
        </dl>
    </section>
</template>