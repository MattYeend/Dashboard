<script setup lang="ts">
import type { User } from '@/types';

interface Props {
    user: User;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Role details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Role</dt>
                <dd class="text-sm capitalize">
                    {{ user.role.replace('_', ' ') }}
                </dd>
            </div>
        </dl>
    </div>
</template>
