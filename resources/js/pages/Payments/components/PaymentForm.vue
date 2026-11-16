<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import type { PayableInvoiceOption, PaymentMethodOption } from '@/types';

import PaymentBasicDetailsForm from './PaymentBasicDetailsForm.vue';
import PaymentReferenceDetailsForm from './PaymentReferenceDetailsForm.vue';
import type { PaymentFormData } from '@/types/forms';

defineProps<{
    errors: Partial<Record<keyof PaymentFormData, string>>;
    processing: boolean;
    invoices: PayableInvoiceOption[];
    methods: PaymentMethodOption[];
    lockInvoice: boolean;
    lockAmount: boolean;
    submitLabel: string;
    cancelHref: string;
}>();

const emit = defineEmits<{
    (event: 'submit'): void;
}>();

const form = defineModel<PaymentFormData>({ required: true });
</script>

<template>
    <form class="grid gap-8" @submit.prevent="emit('submit')">
        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Payment details</h2>
            <PaymentBasicDetailsForm
                v-model="form"
                :errors="errors"
                :invoices="invoices"
                :methods="methods"
                :lock-invoice="lockInvoice"
                :lock-amount="lockAmount"
            />
        </section>

        <section class="grid gap-4">
            <h2 class="text-lg font-semibold">Reference</h2>
            <PaymentReferenceDetailsForm v-model="form" :errors="errors" />
        </section>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">{{ submitLabel }}</Button>
            <Button as-child variant="outline">
                <Link :href="cancelHref">Cancel</Link>
            </Button>
        </div>
    </form>
</template>