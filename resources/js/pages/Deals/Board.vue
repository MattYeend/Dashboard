<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DealStageColumn from '@/pages/Deals/components/DealStageColumn.vue';
import { updateStage } from '@/routes/deals';
import type { Deal } from '@/types';

interface BoardStage {
    id: number;
    name: string;
    deals: Deal[];
}

const props = defineProps<{
    pipelineName: string;
    stages: BoardStage[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Deals', href: '/deals' },
            { title: 'Board', href: '/deals/board' },
        ],
    },
});

const stages = ref<BoardStage[]>(props.stages);

function onDealMoved(dealId: number, stageId: number): void {
    router.patch(
        updateStage.url(dealId),
        { stage_id: stageId },
        { preserveScroll: true, preserveState: true },
    );
}
</script>

<template>
    <Head :title="`${pipelineName} board`" />

    <div class="flex gap-4 overflow-x-auto pb-4">
        <DealStageColumn
            v-for="stage in stages"
            :key="stage.id"
            :stage="stage"
            @deal-moved="onDealMoved"
        />
    </div>
</template>
