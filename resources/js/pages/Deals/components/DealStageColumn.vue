<script setup lang="ts">
import draggable from 'vuedraggable';
import DealCard from '@/pages/Deals/components/DealCard.vue';
import type { Deal } from '@/types';

interface BoardStage {
    id: number;
    name: string;
    deals: Deal[];
}

const props = defineProps<{
    stage: BoardStage;
}>();

const emit = defineEmits<{
    (event: 'deal-moved', dealId: number, stageId: number): void;
}>();

function onChange(event: { added?: { element: Deal } }): void {
    if (event.added) {
        emit('deal-moved', event.added.element.id, props.stage.id);
    }
}
</script>

<template>
    <div class="flex w-72 shrink-0 flex-col rounded border border-gray-500">
        <div class="border-b border-gray-500 px-3 py-2">
            <h3 class="text-sm font-semibold text-gray-300">
                {{ stage.name }}
            </h3>
            <p class="text-xs text-gray-400">
                {{ stage.deals.length }} deal
                {{ stage.deals.length === 1 ? '' : 's' }}
            </p>
        </div>

        <draggable
            :list="stage.deals"
            item-key="id"
            group="deals-board"
            class="flex min-h-16 flex-1 flex-col gap-2 p-2"
            @change="onChange"
        >
            <template #item="{ element }">
                <DealCard :deal="element" />
            </template>
        </draggable>
    </div>
</template>
