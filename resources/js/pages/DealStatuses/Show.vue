<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
    edit as dealStatusesEdit,
    destroy as dealStatusesDestroy,
    index as dealStatusesIndex,
} from '@/routes/order-statuses';
import type { DealStatus, PermissionsMeta } from '@/types';
import DealStatusAuditDetails from './components/DealStatusAuditDetails.vue';
import DealStatusBasicDetails from './components/DealStatusBasicDetails.vue';
import DealStatusColourDetails from './components/DealStatusColourDetails.vue';

interface Props {
    dealStatus: DealStatus;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.dealStatus?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.dealStatus?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(dealStatusesDestroy.url(props.dealStatus.id), {
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
                    {{ dealStatus.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="dealStatusesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="dealStatusesEdit.url(dealStatus.id)"
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
                <DealStatusBasicDetails :deal-status="dealStatus" />
                <DealStatusColourDetails :deal-status="dealStatus" />
                <DealStatusAuditDetails :deal-status="dealStatus" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete deal status"
            description="This deal status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
