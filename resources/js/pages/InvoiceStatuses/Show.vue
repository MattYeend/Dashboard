<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { InvoiceStatus, PermissionsMeta } from '@/types';
import InvoiceStatusAuditDetails from './components/InvoiceStatusAuditDetails.vue';
import InvoiceStatusBasicDetails from './components/InvoiceStatusBasicDetails.vue';
import InvoiceStatusColourDetails from './components/InvoiceStatusColourDetails.vue';
import {
    edit as invoiceStatusesEdit,
    destroy as invoiceStatusesDestroy,
    index as invoiceStatusesIndex,
} from '@/routes/invoice-statuses';

interface Props {
    invoiceStatus: InvoiceStatus;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.invoiceStatus?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.invoiceStatus?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(invoiceStatusesDestroy.url(props.invoiceStatus.id), {
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
                    {{ invoiceStatus.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="invoiceStatusesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="invoiceStatusesEdit.url(invoiceStatus.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="permissions_meta.can_create"
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <InvoiceStatusBasicDetails :invoice-status="invoiceStatus" />
                <InvoiceStatusColourDetails :invoice-status="invoiceStatus" />
                <InvoiceStatusAuditDetails :invoice-status="invoiceStatus" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete invoice status"
            description="This invoice status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
