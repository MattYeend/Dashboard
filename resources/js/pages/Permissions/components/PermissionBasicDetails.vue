<script setup lang="ts">
import type { Permission } from '@/types';

defineProps<{ permission: Permission }>();
</script>

<template>
    <dl class="grid grid-cols-2 gap-4">
        <div>
            <dt class="text-xs text-gray-400">Name</dt>
            <dd class="text-sm">{{ permission.name }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Guard</dt>
            <dd class="text-sm">{{ permission.guard_name }}</dd>
        </div>
    </dl>
</template>
