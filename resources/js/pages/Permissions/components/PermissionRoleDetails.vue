<script setup lang="ts">
import type { Permission } from '@/types';

defineProps<{ permission: Permission }>();
</script>

<template>
    <dl>
        <dt class="text-xs text-gray-400">Assigned roles</dt>
        <dd class="text-sm">
            <span v-if="!permission.roles?.length" class="text-gray-400">None</span>
            <span v-else>{{ permission.roles.map((role) => role.name).join(', ') }}</span>
        </dd>
    </dl>
</template>
