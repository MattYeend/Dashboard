<script setup lang="ts">
import type { Organisation } from '@/types';

interface Props {
    organisation: Organisation;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">
            Organisation details
        </h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">{{ organisation.name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Slug</dt>
                <dd class="text-sm">{{ organisation.slug }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Members</dt>
                <dd class="text-sm">{{ organisation.members_count ?? 0 }}</dd>
            </div>
        </dl>
    </div>
</template>
