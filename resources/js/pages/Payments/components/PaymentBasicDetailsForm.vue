<script setup lang="ts">
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatMoney } from '@/lib/money';
import type { PayableInvoiceOption, PaymentMethodOption } from '@/types';

import type { PaymentFormData } from '@/types/forms';

const props = defineProps<{
    errors: Partial<Record<keyof PaymentFormData, string>>;
    invoices: PayableInvoiceOption[];
    methods: PaymentMethodOption[];
    lockInvoice: boolean;
    lockAmount: boolean;
}>();

const form = defineModel<PaymentFormData>({ required: true });

const selectedInvoice = computed(
    () => props.invoices.find((invoice) => String(invoice.id) === form.value.invoice_id) ?? null,
);
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="grid gap-2 md:col-span-2">
            <Label for="invoice_id">Invoice</Label>
            <Select v-model="form.invoice_id" :disabled="lockInvoice">
                <SelectTrigger id="invoice_id">
                    <SelectValue placeholder="Select an invoice" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="invoice in invoices"
                        :key="invoice.id"
                        :value="String(invoice.id)"
                    >
                        {{ invoice.invoice_number }} (due {{ formatMoney(invoice.balance_due) }})
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.invoice_id" />
            <p v-if="selectedInvoice" class="text-sm text-muted-foreground">
                Outstanding balance: {{ formatMoney(selectedInvoice.balance_due) }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="amount">Amount (GBP)</Label>
            <Input
                id="amount"
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0.01"
                :max="selectedInvoice?.balance_due"
                :disabled="lockAmount"
            />
            <InputError :message="errors.amount" />
        </div>

        <div class="grid gap-2">
            <Label for="method">Method</Label>
            <Select v-model="form.method">
                <SelectTrigger id="method">
                    <SelectValue placeholder="Select a method" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="option in methods" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.method" />
        </div>

        <div class="grid gap-2">
            <Label for="paid_at">Date received</Label>
            <Input id="paid_at" v-model="form.paid_at" type="datetime-local" />
            <InputError :message="errors.paid_at" />
        </div>
    </div>
</template>