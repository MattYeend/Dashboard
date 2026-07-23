<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
    errors: Partial<InertiaFormProps<InvoiceFormData>['errors']>;
}

defineProps<Props>();

const issueDate = defineModel<string | null>('issueDate', { default: null });
const dueDate = defineModel<string | null>('dueDate', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="issue_date">Issue Date</Label>
            <Input
                id="issue_date"
                :model-value="issueDate ?? ''"
                type="date"
                class="mt-1 block w-full"
                @update:model-value="issueDate = ($event as string) || null"
            />
            <InputError :message="errors.issue_date" />
        </div>

        <div>
            <Label for="due_date">Due Date</Label>
            <Input
                id="due_date"
                :model-value="dueDate ?? ''"
                type="date"
                class="mt-1 block w-full"
                @update:model-value="dueDate = ($event as string) || null"
            />
            <InputError :message="errors.due_date" />
        </div>
    </div>
</template>
