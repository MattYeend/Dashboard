<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import InvoiceItemBasicDetailsForm from '@/pages/InvoiceItems/components/InvoiceItemBasicDetailsForm.vue';
import { index as invoiceItemsIndex } from '@/routes/invoices/items';

interface InvoiceItemFormData {
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate: number;
    position: number;
}

interface Props {
    invoiceId: number;
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<InvoiceItemFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const description = defineModel<string>('description', { required: true });
const quantity = defineModel<number>('quantity', { required: true });
const unitPrice = defineModel<number>('unitPrice', { required: true });
const taxRate = defineModel<number>('taxRate', { required: true });
const position = defineModel<number>('position', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <InvoiceItemBasicDetailsForm
            v-model:description="description"
            v-model:quantity="quantity"
            v-model:unit-price="unitPrice"
            v-model:tax-rate="taxRate"
            v-model:position="position"
            :errors="errors"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="invoiceItemsIndex.url({ invoice: invoiceId })">
                    Cancel
                </Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Item' : 'Create Item' }}
            </Button>
        </div>
    </form>
</template>
