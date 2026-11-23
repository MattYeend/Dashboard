<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import {
    edit as campaignSegmentsEdit,
    destroy as campaignSegmentsDestroy,
    index as campaignSegmentsIndex,
} from '@/routes/campaign-segments';
import type { CampaignSegment, PermissionsMeta } from '@/types';
import CampaignSegmentAudienceDetails from './components/CampaignSegmentAudienceDetails.vue';
import CampaignSegmentAuditDetails from './components/CampaignSegmentAuditDetails.vue';
import CampaignSegmentBasicDetails from './components/CampaignSegmentBasicDetails.vue';
import CampaignSegmentCriteriaDetails from './components/CampaignSegmentCriteriaDetails.vue';
 
interface Props {
    campaignSegment: CampaignSegment;
    permissions_meta: PermissionsMeta;
    canEdit?: boolean;
}
 
const props = defineProps<Props>();
 
const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);
 
function requestDestroy(): void {
    if (!props.campaignSegment?.id) {
        return;
    }
 
    deleteDialogOpen.value = true;
}
 
function destroy(): void {
    if (!props.campaignSegment?.id) {
        return;
    }
 
    deleteProcessing.value = true;
 
    router.delete(campaignSegmentsDestroy.url(props.campaignSegment.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>
 
<template>
    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ campaignSegment.name }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="campaignSegmentsIndex.url()">Back</Link>
                    </Button>
                    <Button v-if="canEdit" as-child>
                        <Link
                            :href="campaignSegmentsEdit.url(campaignSegment.id)"
                            >Edit</Link
                        >
                    </Button>
                    <button
                        v-if="permissions_meta.can_delete !== false"
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>
 
            <div class="space-y-6">
                <CampaignSegmentBasicDetails :campaign-segment="campaignSegment" />
                <CampaignSegmentCriteriaDetails
                    :campaign-segment="campaignSegment"
                />
                <CampaignSegmentAudienceDetails
                    :campaign-segment="campaignSegment"
                />
                <CampaignSegmentAuditDetails :campaign-segment="campaignSegment" />
            </div>
        </div>
 
        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete campaign segment"
            description="This campaign segment will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>