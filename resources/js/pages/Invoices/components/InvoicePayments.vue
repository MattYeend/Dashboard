<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { formatDate, formatMoney } from '@/lib/money';
import RefundPaymentDialog from '@/pages/Payments/components/RefundPaymentDialog.vue';
import { create, show } from '@/routes/payments';
import type { Invoice, Payment } from '@/types';

defineProps<{
    invoice: Invoice;
    payments: Payment[];
    canRecordPayment: boolean;
}>();

const refunding = ref<Payment | null>(null);
const dialogOpen = ref(false);

/**
 * Open the refund dialog for a payment.
 */
function startRefund(payment: Payment): void {
    refunding.value = payment;
    dialogOpen.value = true;
}
</script>

<template>
    <section class="grid gap-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Payments</h2>
            <Button v-if="canRecordPayment" as-child size="sm">
                <Link :href="create.url({ query: { invoice_id: invoice.id } })"
                    >Record payment</Link
                >
            </Button>
        </div>

        <p v-if="payments.length === 0" class="text-sm text-muted-foreground">
            No payments recorded yet.
        </p>

        <ul v-else class="grid gap-2">
            <li
                v-for="payment in payments"
                :key="payment.id"
                class="flex items-center justify-between gap-4"
            >
                <div>
                    <Link
                        :href="show.url(payment.id)"
                        class="text-sm font-medium underline"
                    >
                        {{ payment.type === 'refund' ? 'Refund' : 'Payment' }}
                        {{ formatDate(payment.paid_at) }}
                    </Link>
                    <p class="text-sm text-muted-foreground">
                        {{ payment.method_label
                        }}<span v-if="payment.reference"
                            >, {{ payment.reference }}</span
                        >
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium">
                        {{ payment.type === 'refund' ? '-' : ''
                        }}{{ formatMoney(payment.amount, payment.currency) }}
                    </span>
                    <Button
                        v-if="payment.can_refund"
                        size="sm"
                        variant="outline"
                        @click="startRefund(payment)"
                    >
                        Refund
                    </Button>
                </div>
            </li>
        </ul>

        <RefundPaymentDialog
            v-if="refunding"
            v-model:open="dialogOpen"
            :payment="refunding"
        />
    </section>
</template>
