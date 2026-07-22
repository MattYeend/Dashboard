<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
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
import type { InvoiceStatus, UserOption } from '@/types';

interface InvoiceFormData {
    invoice_number: string;
    company_id: number | null;
    order_id: number | null;
    status_id: number | null;
    issue_date: string | null;
    due_date: string | null;
    subtotal: number | null;
    tax_total: number | null;
    total: number | null;
    currency: string;
    notes: string | null;
    contact: {
        phone: string | null;
        email: string | null;
        address: string | null;
        city: string | null;
        postal_code: string | null;
        country: string | null;
    };
}

interface Props {
    statuses: InvoiceStatus[];
    companies: UserOption[];
    errors: Partial<InertiaFormProps<InvoiceFormData>['errors']>;
}

defineProps<Props>();

const invoiceNumber = defineModel<string>('invoiceNumber', { required: true });
const companyId = defineModel<number | null>('companyId', { default: null });
const statusId = defineModel<number | null>('statusId', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="invoice_number"
                >Invoice Number <span class="text-destructive">*</span></Label
            >
            <Input
                id="invoice_number"
                v-model="invoiceNumber"
                type="text"
                class="mt-1 block w-full"
                placeholder="INV-000001"
            />
            <InputError :message="errors.invoice_number" />
        </div>

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
            <Label for="status_id">Status</Label>
            <Select v-model="statusId">
                <SelectTrigger id="status_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select a status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="status in statuses"
                        :key="status.id"
                        :value="status.id"
                    >
                        {{ status.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.status_id" />
        </div>
    </div>
</template>
