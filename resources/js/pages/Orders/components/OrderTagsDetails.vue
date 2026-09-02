<script setup lang="ts">
import TagBadge from '@/components/TagBadge.vue';
import type { Order } from '@/types';

interface Props {
    order: Order;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Tags</h2>

        <div v-if="!order.tags?.length" class="text-sm text-gray-400">-</div>
        <div v-else class="flex flex-wrap gap-2">
            <TagBadge
                v-for="tag in order.tags"
                :key="tag.id"
                :name="tag.name"
            />
        </div>
    </div>
</template>
