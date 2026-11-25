<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import {
    destroy as campaignsDestroy,
    edit as campaignsEdit,
    index as campaignsIndex,
} from '@/routes/campaigns';
import { index as campaignRecipientsIndex } from '@/routes/campaigns/campaign-recipients';
import type { Campaign, PermissionsMeta } from '@/types';
import CampaignAuditDetails from './components/CampaignAuditDetails.vue';
import CampaignBasicDetails from './components/CampaignBasicDetails.vue';
import CampaignDeliveryDetails from './components/CampaignDeliveryDetails.vue';
import CampaignScheduleDetails from './components/CampaignScheduleDetails.vue';
import CampaignSenderDetails from './components/CampaignSenderDetails.vue';
import CampaignStatusBadge from './components/CampaignStatusBadge.vue';

interface Props {
    campaign: Campaign;
    permissions_meta: PermissionsMeta;
    canEdit?: boolean;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.campaign?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.campaign?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(campaignsDestroy.url(props.campaign.id), {
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
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-gray-300">
                        {{ campaign.name }}
                    </h1>
                    <CampaignStatusBadge :status="campaign.status" />
                </div>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="campaignsIndex.url()">Back</Link>
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="campaignRecipientsIndex.url(campaign.id)">
                            Recipients
                        </Link>
                    </Button>
                    <Button v-if="canEdit" as-child>
                        <Link :href="campaignsEdit.url(campaign.id)">Edit</Link>
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
                <CampaignBasicDetails :campaign="campaign" />
                <CampaignSenderDetails :campaign="campaign" />
                <CampaignScheduleDetails :campaign="campaign" />
                <CampaignDeliveryDetails :campaign="campaign" />
                <CampaignAuditDetails :campaign="campaign" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete campaign"
            description="This campaign will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
