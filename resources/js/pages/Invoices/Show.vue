<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import InvoiceAuditDetails from '@/pages/Invoices/components/InvoiceAuditDetails.vue';
import InvoiceBasicDetails from '@/pages/Invoices/components/InvoiceBasicDetails.vue';
import InvoiceContactDetails from '@/pages/Invoices/components/InvoiceContactDetails.vue';
import InvoiceDateDetails from '@/pages/Invoices/components/InvoiceDateDetails.vue';
import InvoiceTotalsDetails from '@/pages/Invoices/components/InvoiceTotalsDetails.vue';
import type { Invoice, PermissionsMeta } from '@/types';
import {
    edit as invoicesEdit,
    destroy as invoicesDestroy,
    index as invoicesIndex,
    send as invoicesSend,
    markAsPaid as invoicesMarkAsPaid,
    markAsUnpaid as invoicesMarkAsUnpaid,
} from '@/routes/invoices';

interface Props {
    invoice: Invoice;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);
const sendProcessing = ref(false);
const markPaidProcessing = ref(false);
const markUnpaidProcessing = ref(false);

function requestDestroy(): void {
    if (!props.invoice?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.invoice?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(invoicesDestroy.url(props.invoice.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}

function send(): void {
    sendProcessing.value = true;

    router.post(
        invoicesSend.url(props.invoice.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                sendProcessing.value = false;
            },
        },
    );
}

function markAsPaid(): void {
    markPaidProcessing.value = true;

    router.post(
        invoicesMarkAsPaid.url(props.invoice.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                markPaidProcessing.value = false;
            },
        },
    );
}

function markAsUnpaid(): void {
    markUnpaidProcessing.value = true;

    router.post(
        invoicesMarkAsUnpaid.url(props.invoice.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                markUnpaidProcessing.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ invoice.invoice_number }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="invoicesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                        :disabled="sendProcessing"
                        @click="send"
                    >
                        Send
                    </button>
                    <button
                        v-if="!invoice.paid_at"
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                        :disabled="markPaidProcessing"
                        @click="markAsPaid"
                    >
                        Mark as Paid
                    </button>
                    <button
                        v-else
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                        :disabled="markUnpaidProcessing"
                        @click="markAsUnpaid"
                    >
                        Mark as Unpaid
                    </button>
                    <Link
                        :href="invoicesEdit.url(invoice.id)"
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
                <InvoiceBasicDetails :invoice="invoice" />
                <InvoiceContactDetails :invoice="invoice" />
                <InvoiceDateDetails :invoice="invoice" />
                <InvoiceTotalsDetails :invoice="invoice" />
                <InvoiceAuditDetails :invoice="invoice" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete invoice"
            description="This invoice will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
