<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface InvoiceItemFormData {
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate: number;
    position: number;
}

interface Props {
    errors: Partial<InertiaFormProps<InvoiceItemFormData>['errors']>;
}

defineProps<Props>();

const description = defineModel<string>('description', { required: true });
const quantity = defineModel<number>('quantity', { required: true });
const unitPrice = defineModel<number>('unitPrice', { required: true });
const taxRate = defineModel<number>('taxRate', { required: true });
const position = defineModel<number>('position', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="description"
                >Description <span class="text-destructive">*</span></Label
            >
            <Input
                id="description"
                v-model="description"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter item description"
            />
            <InputError :message="errors.description" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <Label for="quantity"
                    >Quantity <span class="text-destructive">*</span></Label
                >
                <Input
                    id="quantity"
                    :model-value="quantity"
                    type="number"
                    min="1"
                    class="mt-1 block w-full"
                    @update:model-value="
                        quantity = $event === '' ? 1 : Number($event)
                    "
                />
                <InputError :message="errors.quantity" />
            </div>

            <div>
                <Label for="unit_price"
                    >Unit Price (pence)
                    <span class="text-destructive">*</span></Label
                >
                <Input
                    id="unit_price"
                    :model-value="unitPrice"
                    type="number"
                    min="0"
                    class="mt-1 block w-full"
                    @update:model-value="
                        unitPrice = $event === '' ? 0 : Number($event)
                    "
                />
                <InputError :message="errors.unit_price" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <Label for="tax_rate"
                    >Tax Rate (%) <span class="text-destructive">*</span></Label
                >
                <Input
                    id="tax_rate"
                    :model-value="taxRate"
                    type="number"
                    min="0"
                    step="0.01"
                    class="mt-1 block w-full"
                    @update:model-value="
                        taxRate = $event === '' ? 0 : Number($event)
                    "
                />
                <InputError :message="errors.tax_rate" />
            </div>

            <div>
                <Label for="position">Position</Label>
                <Input
                    id="position"
                    :model-value="position"
                    type="number"
                    min="0"
                    class="mt-1 block w-full"
                    @update:model-value="
                        position = $event === '' ? 0 : Number($event)
                    "
                />
                <InputError :message="errors.position" />
            </div>
        </div>
    </div>
</template>
