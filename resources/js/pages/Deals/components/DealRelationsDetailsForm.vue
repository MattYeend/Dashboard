<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Company, Invoice } from '@/types';

interface DealRelationsFormData {
    company_id: number | null;
    invoice_id: number | null;
}

interface Props {
    companies: Company[];
    invoices: Invoice[];
    errors: Partial<InertiaFormProps<DealRelationsFormData>['errors']>;
}

defineProps<Props>();

const companyId = defineModel<number | null>('companyId', { default: null });
const invoiceId = defineModel<number | null>('invoiceId', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="company_id">Company</Label>
            <Select v-model="companyId">
                <SelectTrigger id="company_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select a company" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="company in companies"
                        :key="company.id"
                        :value="company.id"
                    >
                        {{ company.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.company_id" />
        </div>

        <div>
            <Label for="invoice_id">Invoice</Label>
            <Select v-model="invoiceId">
                <SelectTrigger id="invoice_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select an invoice" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="invoice in invoices"
                        :key="invoice.id"
                        :value="invoice.id"
                    >
                        {{ invoice.invoice_number }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.invoice_id" />
        </div>
    </div>
</template>