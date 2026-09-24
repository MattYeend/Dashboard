<script setup lang="ts">
import type { User } from '@/types';

defineProps<{
    user: User;
}>();
</script>

<template>
    <section class="rounded-lg border border-gray-500 p-4">
        <h2 class="mb-3 text-sm font-medium text-gray-300">Basic details</h2>
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">{{ user.name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Email</dt>
                <dd class="text-sm">{{ user.email }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Role</dt>
                <dd class="text-sm">{{ user.role }}</dd>
            </div>
        </dl>
    </section>
</template>