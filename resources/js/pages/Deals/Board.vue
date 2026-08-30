<script setup lang="ts">
// After:
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AddStageColumn from '@/pages/Deals/components/AddStageColumn.vue';
import DealStageColumn from '@/pages/Deals/components/DealStageColumn.vue';
import { index as dealsIndex, updateStage as dealsUpdateStage } from '@/routes/deals';
import type { Deal } from '@/types';

interface BoardStage {
    id: number;
    name: string;
    deals: Deal[];
}

interface Props {
    pipelineId: number; // NEW — needed to POST a new stage to the right pipeline
    pipelineName: string;
    stages: BoardStage[];
}

const props = defineProps<Props>();

const stages = ref<BoardStage[]>(props.stages);

function onDealMoved(dealId: number, stageId: number): void {
    router.patch(
        dealsUpdateStage.url(dealId),
        { stage_id: stageId },
        { preserveScroll: true, preserveState: true },
    );
}

function onStageAdded(stage: BoardStage): void {
    stages.value.push(stage);
}
</script>

<template>
    <Head :title="`${pipelineName} board`" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-200">
                    {{ pipelineName }} Board
                </h1>
                <Link :href="dealsIndex.url()" class="text-sm text-gray-400 hover:text-gray-200">
                    View as list
                </Link>
            </div>

            <div class="flex gap-4 overflow-x-auto pb-4">
                <DealStageColumn
                    v-for="stage in stages"
                    :key="stage.id"
                    :stage="stage"
                    @deal-moved="onDealMoved"
                />
                <AddStageColumn :pipeline-id="pipelineId" @stage-added="onStageAdded" />
            </div>
        </div>
    </div>
</template>
