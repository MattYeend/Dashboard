<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import {
    edit as campaignStatusesEdit,
    destroy as campaignStatusesDestroy,
    index as campaignStatusesIndex,
} from '@/routes/campaign-statuses';
import type { CampaignStatus, PermissionsMeta } from '@/types';
import CampaignStatusAuditDetails from './components/CampaignStatusAuditDetails.vue';
import CampaignStatusBasicDetails from './components/CampaignStatusBasicDetails.vue';
import CampaignStatusColourDetails from './components/CampaignStatusColourDetails.vue';

interface Props {
    campaignStatus: CampaignStatus;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.campaignStatus?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.campaignStatus?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(campaignStatusesDestroy.url(props.campaignStatus.id), {
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
                    {{ campaignStatus.title }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="campaignStatusesIndex.url()">Back</Link>
                    </Button>
                    <Button as-child>
                        <Link
                            :href="campaignStatusesEdit.url(campaignStatus.id)"
                            >Edit</Link
                        >
                    </Button>
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
                <CampaignStatusBasicDetails :campaign-status="campaignStatus" />
                <CampaignStatusColourDetails
                    :campaign-status="campaignStatus"
                />
                <CampaignStatusAuditDetails :campaign-status="campaignStatus" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete campaign status"
            description="This campaign status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>