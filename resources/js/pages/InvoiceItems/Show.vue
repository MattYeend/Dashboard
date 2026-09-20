<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import InvoiceItemAuditDetails from '@/pages/InvoiceItems/components/InvoiceItemAuditDetails.vue';
import InvoiceItemBasicDetails from '@/pages/InvoiceItems/components/InvoiceItemBasicDetails.vue';
import {
    edit as invoiceItemsEdit,
    destroy as invoiceItemsDestroy,
    index as invoiceItemsIndex,
} from '@/routes/invoices/items';
import type { Invoice, InvoiceItem, PermissionsMeta } from '@/types';

interface Props {
    invoice: Invoice;
    item: InvoiceItem;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.item?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.item?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(
        invoiceItemsDestroy.url({
            invoice: props.invoice.id,
            invoiceItem: props.item.id,
        }),
        {
            onFinish: () => {
                deleteProcessing.value = false;
                deleteDialogOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ item.description }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Link
                        :href="invoiceItemsIndex.url({ invoice: invoice.id })"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="
                            invoiceItemsEdit.url({
                                invoice: invoice.id,
                                invoiceItem: item.id,
                            })
                        "
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
                <InvoiceItemBasicDetails :item="item" />
                <InvoiceItemAuditDetails :item="item" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete invoice item"
            description="This invoice item will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
