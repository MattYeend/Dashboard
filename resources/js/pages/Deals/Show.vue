<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DealAuditDetails from '@/pages/Deals/components/DealAuditDetails.vue';
import DealBasicDetails from '@/pages/Deals/components/DealBasicDetails.vue';
import DealPipelineDetails from '@/pages/Deals/components/DealPipelineDetails.vue';
import DealRelationsDetails from '@/pages/Deals/components/DealRelationsDetails.vue';
import DealValueDetails from '@/pages/Deals/components/DealValueDetails.vue';
import type { Deal } from '@/types';
import {
    edit as dealsEdit,
    destroy as dealsDestroy,
    index as dealsIndex,
} from '@/routes/deals';

interface Props {
    deal: Deal;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(dealsDestroy.url(props.deal.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ deal.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="dealsIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="dealsEdit.url(props.deal.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <DealBasicDetails :deal="deal" />
                <DealPipelineDetails :deal="deal" />
                <DealValueDetails :deal="deal" />
                <DealRelationsDetails :deal="deal" />
                <DealAuditDetails :deal="deal" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete deal"
            description="This deal will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
