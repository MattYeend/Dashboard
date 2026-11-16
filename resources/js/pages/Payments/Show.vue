<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import PaymentAuditDetails from '@/pages/Payments/components/PaymentAuditDetails.vue';
import PaymentBasicDetails from '@/pages/Payments/components/PaymentBasicDetails.vue';
import PaymentReferenceDetails from '@/pages/Payments/components/PaymentReferenceDetails.vue';
import PaymentRefundsList from '@/pages/Payments/components/PaymentRefundsList.vue';
import RefundPaymentDialog from '@/pages/Payments/components/RefundPaymentDialog.vue';
import { edit, index } from '@/routes/payments';
import type { Payment } from '@/types';

defineProps<{
    payment: Payment;
    refunds: Payment[];
}>();

const refundOpen = ref(false);
</script>

<template>
    <Head :title="`Payment #${payment.id}`" />

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    Payment #{{ payment.id }}
                </h1>
                <div class="ml-auto flex flex-wrap gap-2">
                    <Button as-child variant="outline">
                        <Link :href="index.url()">Back</Link>
                    </Button>
                    <Button
                        v-if="payment.can_refund"
                        variant="outline"
                        @click="refundOpen = true"
                    >
                        Refund
                    </Button>
                    <Button
                        v-if="!payment.is_locked && !payment.deleted_at"
                        as-child
                    >
                        <Link :href="edit.url(payment.id)">Edit</Link>
                    </Button>
                </div>
            </div>

            <div class="space-y-6">
                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Payment details</h2>
                    <PaymentBasicDetails :payment="payment" />
                </section>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Reference</h2>
                    <PaymentReferenceDetails :payment="payment" />
                </section>

                <section v-if="payment.type === 'payment'" class="grid gap-4">
                    <h2 class="text-lg font-semibold">Refunds</h2>
                    <PaymentRefundsList :refunds="refunds" />
                </section>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Audit</h2>
                    <PaymentAuditDetails :payment="payment" />
                </section>
            </div>
        </div>

        <RefundPaymentDialog
            v-if="payment.can_refund"
            v-model:open="refundOpen"
            :payment="payment"
        />
    </div>
</template>