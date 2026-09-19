<script setup lang="ts">
import type { Permission } from '@/types';

defineProps<{ permission: Permission }>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Role details</h2>

        <dl>
            <dt class="text-xs text-gray-400">Assigned roles</dt>
            <dd class="text-sm">
                <span v-if="!permission.roles?.length" class="text-gray-400">
                    None
                </span>
                <span v-else>
                    {{ permission.roles.map((role) => role.name).join(', ') }}
                </span>
            </dd>
        </dl>
    </div>
</template>
