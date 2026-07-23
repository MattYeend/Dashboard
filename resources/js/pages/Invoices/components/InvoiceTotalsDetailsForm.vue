<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

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

const subtotal = defineModel<number | null>('subtotal', { default: null });
const taxTotal = defineModel<number | null>('taxTotal', { default: null });
const total = defineModel<number | null>('total', { default: null });
const currency = defineModel<string>('currency', { required: true });
const notes = defineModel<string | null>('notes', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <Label for="subtotal">Subtotal (pence)</Label>
                <Input
                    id="subtotal"
                    :model-value="subtotal ?? ''"
                    type="number"
                    class="mt-1 block w-full"
                    @update:model-value="
                        subtotal = $event === '' ? null : Number($event)
                    "
                />
                <InputError :message="errors.subtotal" />
            </div>

            <div>
                <Label for="tax_total">Tax Total (pence)</Label>
                <Input
                    id="tax_total"
                    :model-value="taxTotal ?? ''"
                    type="number"
                    class="mt-1 block w-full"
                    @update:model-value="
                        taxTotal = $event === '' ? null : Number($event)
                    "
                />
                <InputError :message="errors.tax_total" />
            </div>

            <div>
                <Label for="total">Total (pence)</Label>
                <Input
                    id="total"
                    :model-value="total ?? ''"
                    type="number"
                    class="mt-1 block w-full"
                    @update:model-value="
                        total = $event === '' ? null : Number($event)
                    "
                />
                <InputError :message="errors.total" />
            </div>
        </div>

        <div>
            <Label for="currency"
                >Currency <span class="text-destructive">*</span></Label
            >
            <Input
                id="currency"
                v-model="currency"
                type="text"
                maxlength="3"
                class="mt-1 block w-full uppercase"
                placeholder="GBP"
            />
            <InputError :message="errors.currency" />
        </div>

        <div>
            <Label for="notes">Notes</Label>
            <Textarea
                id="notes"
                :model-value="notes ?? ''"
                class="mt-1 block w-full"
                rows="4"
                placeholder="Enter invoice notes"
                @update:model-value="notes = ($event as string) || null"
            />
            <InputError :message="errors.notes" />
        </div>
    </div>
</template>
